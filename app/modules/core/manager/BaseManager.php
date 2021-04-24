<?php

namespace Tymy\Module\Core\Manager;

use Exception;
use Nette\Application\AbortException;
use Nette\Database\Explorer;
use Nette\Database\IRow;
use Nette\Database\Table\ActiveRow;
use Nette\Security\User;
use Nette\Utils\DateTime;
use PDOException;
use Tymy\App\Model\Supplier;
use Tymy\Module\Core\Exception\DBException;
use Tymy\Module\Core\Factory\ManagerFactory;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Core\Model\Field;
use Tymy\Module\Core\Model\Filter;
use Tymy\Module\Core\Model\Order;
use Tymy\Module\User\Model\User as UserEntity;

/**
 * Description of BaseManager
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 5. 6. 2020
 */
abstract class BaseManager
{

    public const FILTER_REGEX = "/^([a-zA-Z]+)\s*(<|>|=|<=|>=|!=|#=)\s*([a-zA-Z0-9\\\\.:-]+)$/m";

    protected Explorer $mainDatabase;
    protected Explorer $teamDatabase;
    protected string $teamSysName;
    protected Explorer $database;
    protected Responder $responder;
    protected User $user;
    protected Supplier $supplier;

    /** @var int[] */
    private array $allIdList = [];
    protected ?string $idCol = "id"; //default id column name

    public function __construct(ManagerFactory $managerFactory)
    {
        $this->mainDatabase = $managerFactory->mainDatabase;
        $this->teamDatabase = $managerFactory->teamDatabase;
        $this->database = $this->teamDatabase;
        $this->teamSysName = $managerFactory->teamSysName;
        $this->responder = $managerFactory->responder;
        $this->user = $managerFactory->user;
        $this->supplier = $managerFactory->supplier;
    }

    /** @return Field[] */
    abstract protected function getScheme(): array;

    abstract public function create(array $data, ?int $resourceId = null): BaseModel;

    abstract public function read(int $resourceId, ?int $subResourceId = null): BaseModel;

    abstract public function update(array $data, int $resourceId, ?int $subResourceId = null): BaseModel;

    abstract public function delete(int $resourceId, ?int $subResourceId = null): int;

    /** @return string */
    protected function getTable(): string
    {
        $class = $this->getClassName();
        return $class::TABLE;
    }

    public function getModule(): string
    {
        $class = $this->getClassName();
        return $class::MODULE;
    }

    abstract protected function getClassName(): string;

    abstract public function canRead(BaseModel $entity, int $userId): bool;

    abstract public function canEdit(BaseModel $entity, int $userId): bool;

    /**
     * Get list of user ids, allowed to read given entity
     * @param int|ActiveRow|BusinessCase $record
     * @return int[]
     */
    abstract public function getAllowedReaders(BaseModel $record): array;

    /**
     * Function to quickly obtain all user ids - very neccessary when getAllowedReaders function should return all readers at all
     * @return int[]
     */
    public function getAllUserIds(): array
    {
        if (empty($this->allIdList)) {
            $this->allIdList = $this->database->table(UserEntity::TABLE)->select("id")->fetchPairs(null, "id");
        }

        return $this->allIdList;
    }

    /**
     * Maps one active row to object
     * @param ActiveRow|false $row
     * @return BaseModel
     */
    public function map(?IRow $row, bool $force = false): ?BaseModel
    {
        if (!$row) {
            return null;
        }

        $class = $this->getClassName();

        $object = new $class();

        foreach ($this->getScheme() as $field) {
            $setField = "set" . ucfirst($field->getProperty());
            $column = $field->getColumn();

            if ($row->$column === null && !$field->getMandatory()) {
                continue; //non-mandatory field dont need to set to null, again
            }
            $object->$setField($row->$column);
        }

        $this->metaMap($object);

        return $object; // if this function is called from children, do not postMap (children should do postmap instead)
    }

    /**
     * Maps active rows to array of objects
     * @param array $rows
     * @return BaseModel[]
     */
    public function mapAll(array $rows): array
    {
        $ret = [];
        $this->lastIdList = [];
        foreach ($rows as $row) {
            if ($this->idCol) {
                $this->lastIdList[$row->{$this->idCol}] = $row->{$this->idCol};
            }
            $ret[] = $this->map($row);
        }
        return $ret;
    }

    /**
     * Maps active rows to array of objects, where keys are id fields
     * @param array $rows
     * @return BaseModel[]
     */
    public function mapAllWithId(array $rows): array
    {
        $ret = [];
        foreach ($rows as $row) {
            $ret[$row->id] = $this->map($row);
        }
        return $ret;
    }

    /**
     * Check whether $array cotains all the mandatory fields, specified in mapper
     *
     * @param array $array - Input array to check
     * @throws AbortException
     * @return void
     */
    public function checkInputs(array $array): void
    {
        foreach ($this->getScheme() as $field) {
            if (!$field->getChangeable()) {   //when field is not changeable, its only logical they are not being sent
                continue;
            }

            $input = $field->getAlias() ? $field->getAlias() : $field->getProperty();
            if ($field->getMandatory() && !array_key_exists($input, $array)) {
                $this->responder->E4015_MISSING_URL_INPUT($input);
            }

            if ($field->getNonempty() && empty($array[$input])) {
                $this->responder->E4014_EMPTY_INPUT($input);
            }
        }
    }

    /**
     * Get row from database with given id
     *
     * @param int $id
     * @return ActiveRow $row
     */
    public function getRow(int $id): ?ActiveRow
    {
        return $this->database->table($this->getTable())->where("id", $id)->fetch();
    }

    /**
     * 
     * @param int $id
     * @param bool $force
     * @return BaseModel|null
     */
    public function getById(int $id, bool $force = false): ?BaseModel
    {
        if (!is_numeric($id)) {
            return null;
        }

        return $this->map($this->getRow($id), $force);
    }

    /**
     * Get array of objects
     *
     * @return BaseModel[]
     */
    public function getList(?array $idList = null, string $idField = "id"): array
    {
        $rows = $this->database->table($this->getTable());
        if ($idList !== null) {
            $rows->where($idField, $idList);
        }

        return $this->mapAll($rows->fetchAll());
    }

    /**
     * Get array of objects, where keys are id fields
     *
     * @return BaseModel[]
     */
    public function getIdList(?array $idList = null, string $idField = "id"): array
    {
        $rows = $this->database->table($this->getTable());
        if ($idList !== null) {
            $rows->where($idField, $idList);
        }
        return $this->mapAllWithId($rows->fetchAll());
    }

    public function getListOrder(?array $idList = null, string $idField = "id", $order = null): array
    {
        $rows = $this->database->table($this->getTable());
        if ($idList !== null) {
            $rows->where($idField, $idList);
        }

        if ($order !== null) {
            $rows->order($order);
        }

        return $this->mapAll($rows->fetchAll());
    }

    /**
     * Return field associated with given property
     * Used for filter validation
     *
     * @param string $propertyName
     * @return Field|null
     */
    private function getFieldFromProperty(string $propertyName)
    {
        foreach ($this->getScheme() as $field) {
            $property = $field->getAlias() ? $field->getAlias() : $field->getProperty();
            if ($property === $propertyName) {
                return $field;
            }
        }
        return null;
    }

    /**
     * Check if record, specified by ID, exists - performs a quick db check
     * @param int $id
     * @return bool
     */
    public function exists(int $id, string $table = null): bool
    {
        $table = $table ? $table : $this->getTable();
        return $this->database->table($table)->where("id", $id)->count("id") > 0;
    }

    /**
     * Check if record, specified by list of IDs, exists - check whether supplied count of rows in database matches count of input idList
     * @param int $id
     * @return TRUE if counts matches, array of non-mathcing IDs if not
     */
    public function existsList(array $idList, string $table = null)
    {
        $table = $table ? $table : $this->getTable();
        $ids = $this->database->table($table)->where("id", $idList)->fetchPairs(null, "id");
        if (count($ids) == count($idList)) {
            return true;
        }
        return array_diff($ids, $idList);
    }

    /**
     * Update table row based on given table, id and updates array. Function throws correct exception using class DBException
     * IDColumn can be changed if primary key is different than classic `id`
     *
     * @param string $table Table name
     * @param int $id ID
     * @param array $updates Array of updates
     * @param string $idColumn Caption of primary key column
     * @return int number of affected rows
     * @throws Exception
     */
    protected function updateRecord($table, $id, array $updates, $idColumn = "id")
    {
        try {
            $updated = $this->database->table($table)->where($idColumn, $id)->update($updates);
        } catch (PDOException $exc) {
            throw DBException::from($exc, DBException::TYPE_UPDATE);
        }
        return $updated;
    }

    /**
     * Creates table row based on given table, inserts array. Function throws correct exception using class DBException
     * IDColumn can be changed if primary key is different than classic `id`
     *
     * @param string $table
     * @param array $inserts
     * @param string $idColumn
     * @return ActiveRow
     * @throws Exception
     */
    protected function createRecord($table, array $inserts, $idColumn = "id")
    {
        try {
            $inserted = $this->database->table($table)->insert($inserts);
        } catch (PDOException $exc) {
            throw DBException::from($exc);
        }
        return $inserted;
    }

    /**
     * Delete table row based on given table and id.
     * IDColumn can be changed if primary key is different than classic `id`
     *
     * @param int $id
     * @param string|null $table
     * @param type $idColumn
     * @return int number of affected rows
     * @throws @static.mtd:DBException.from
     */
    protected function deleteRecord(int $id, ?string $table = null, $idColumn = "id")
    {
        try {
            $deleted = $this->database->table($table ?: $this->getTable())->where($idColumn, $id)->delete();
        } catch (PDOException $exc) {
            $e = DBException::from($exc, DBException::TYPE_DELETE);
            $e->withIds($this->database->table($e->fkTable)->where($e->failingField, $id)->fetchPairs(null, "id"));
            $e->fkTable = $this->getModuleFromTable($e->fkTable);
            $e->relatedTable = $this->getModuleFromTable($e->relatedTable);
            throw $e;
        }

        if (!$deleted) {
            $this->responder->E4005_OBJECT_NOT_FOUND($this->getModule(), $id);
        }

        return $id;
    }

    /**
     * Updates record from table using array of fields to update.
     * @param int $id Id of record to update
     * @param array $array Array of fields to update
     * @return int Number of affected rows
     * @throws Exception
     */
    public function updateByArray(int $id, array $array)
    {
        $updates = [];

        foreach ($this->getScheme() as $field) {
            /* @var $field Field */
            if (array_key_exists($field->getProperty(), $array)) {
                $value = $array[$field->getProperty()];
                if (!$field->getChangeable()) {
                    continue;
                }
                if ($field->getNonempty() && $value === null) {
                    $this->responder->E4014_EMPTY_INPUT($field->getProperty());
                }
                $updates[$field->getColumn()] = $value;
            } else {
                //update also update timestamp
                if (!$field->getChangeable()) {
                    if ($field->getColumn() == "usr_mod" && !empty($this->user)) {
                        $updates[$field->getColumn()] = $this->user->id;
                    }
                    if ($field->getColumn() == "dat_mod") {
                        $updates[$field->getColumn()] = new DateTime();
                    }
                }
            }
        }

        return $this->updateRecord($this->getTable(), $id, $updates);
    }

    /**
     * Creates new entity record and returns it on success
     * @param array $array Values to create
     * @return ActiveRow Created row
     * @throws Exception
     */
    public function createByArray($array)
    {
        $created = $this->createRecord($this->getTable(), $this->composeInsertArray($array));

        if (!$created) {
            $this->responder->E4009_CREATE_FAILED($this->getModule());
        }

        return $created;
    }

    /**
     * Use mapper data to compose array of database fields to insert from specified input array
     * @param array $array Input data (usually $this->requestData)
     * @return array Insert output
     */
    public function composeInsertArray($array)
    {
        $inserts = [];

        foreach ($this->getScheme() as $field) {
            /* @var $field Field */
            if($field->getMandatory() && !array_key_exists($field->getProperty(), $array)){
                $this->responder->E4013_MISSING_INPUT($field->getProperty());
            }
            
            $value = $array[$field->getProperty()];
            
            if (!$field->getChangeable()) {
                if (in_array($field->getColumn(), ["user_id", "usr_cre"]) && !empty($this->user)) {
                    $value = $this->user->id;
                } elseif ($field->getColumn() == "dat_cre") {
                    $value = new DateTime();
                } else {
                    continue;
                }
            }

            if ($value === null) {
                if ($field->getNonempty()) {
                    $this->responder->E4014_EMPTY_INPUT($field->getProperty());
                } else {
                    continue; // do not add not-needed null field to insert, so default values from MYSQL settings can be set
                }
            }

            $inserts[$field->getColumn()] = $value;
        }

        return $inserts;
    }

    /**
     * Basic allow function to be overriden - otherwise throw 405:Not allowed
     * @param ?array $data Input data (optional)
     */
    protected function allowCreate(?array &$data = null): void
    {
        //To be overriden
    }

    /**
     * Basic allow function to be overriden - otherwise throw 405:Not allowed
     * @param ?int $recordId Id of record to read (optional)
     */
    protected function allowRead(?int $recordId = null): void
    {
        //To be overriden
    }

    /**
     * Basic allow function to be overriden - otherwise throw 405:Not allowed
     * @param ?int $recordId Id of record to read (optional)
     * @param ?array $data Input data (optional)
     */
    protected function allowUpdate(?int $recordId = null, ?array &$data = null): void
    {
        //To be overriden
    }

    /**
     * Basic allow function to be overriden - otherwise throw 405:Not allowed
     * @param ?int $recordId Id of record to read (optional)
     */
    protected function allowDelete(int $recordId): void
    {
        //To be overriden
    }

    /**
     * Function responds immediately 403:FORBIDDEN for non-admin users
     */
    protected function allowAdmin()
    {
        if (!$this->user->isInRole(UserEntity::ROLE_SUPER)) {
            $this->respondForbidden();
        }
    }

    /**
     * Function to fill entity data with user-related values, like canRead, canEdit, canDelete etc.
     * This function should be called after each entity gets returned from cache - and these props shouldnt be cached at all, obviously
     *
     * This function is meant to be overloaded only. In overloaded functions, <b>DO NOT call parent::metaMap</b>
     *
     * @internal Every override of this function should make this function as fast as possible between one request - using simpleCache when neccessary
     *
     * @param BaseModel $entity
     */
    protected function metaMap(BaseModel &$model, int $userId = null): void
    {
        $model->setHasMeta(false); //when this function is not overloaded, set hasMeta to false - to make always detectible if this entity needs some meta mapping purposes
    }

    /**
     * Get user ids allowed to read given id
     *
     * @param int $modelId
     * @return int[]
     */
    public function getAllowedReadersById(int $modelId)
    {
        return $this->getAllowedReaders($this->getById($modelId));
    }

    protected function respondOk($payload = null)
    {
        $this->responder->A200_OK($payload);
    }

    protected function respondOkCreated($payload = null)
    {
        $this->responder->A201_CREATED($payload);
    }

    protected function respondDeleted($id)
    {
        $this->respondOk(["id" => (int) $id]);
    }

    protected function respondBadRequest($message = null)
    {
        $this->responder->E400_BAD_REQUEST($message);
    }

    protected function respondUnauthorized()
    {
        $this->responder->E401_UNAUTHORIZED();
    }

    protected function respondForbidden(?string $message = null)
    {
        $this->responder->E403_FORBIDDEN($message ?? "Nedostatečná práva");
    }

    protected function respondNotFound()
    {
        $this->responder->E404_NOT_FOUND();
    }

    protected function respondNotAllowed()
    {
        $this->responder->E405_METHOD_NOT_ALLOWED();
    }

    /**
     * Load record using id. Responds with 404 if not found
     *
     * @param int $recordId
     * @return BaseModel
     */
    protected function loadRecord(int $recordId, ?BaseManager $manager = null)
    {
        $record = $manager ? $manager->getById($recordId) : $this->getById($recordId);

        if (!$record) {
            $this->respondNotFound();
        }

        return $record;
    }

    /**
     * Iterate through filterString, parse out all filters and return from them the array or Filter objects for further processing
     * 
     * @param string $filterString
     * @return Filter[]
     */
    protected function filterToArray(string $filterString): array
    {
        $filters = explode("~", $filterString);
        $fParts = null;
        
        if (empty($filters)) {
            return [];
        }

        $conditions = [];
        foreach ($filters as $filter) {
            if (!preg_match(self::FILTER_REGEX, $filter, $fParts)) {
                continue;
            }

            $fieldName = $fParts[1];
            $operator = $fParts[2];
            $value = $fParts[3];

            $columnName = $this->getColumnName($fieldName);
            
            if (!$columnName) {
                $this->responder->E4005_OBJECT_NOT_FOUND("Column", $fieldName);
            }

            $conditions[] = new Filter($columnName, $operator, $value);
        }
        
        return $conditions;
    }
    
    /**
     * Iterate through $orderString, parse out all orders and return from them the array or Order objects for further processing
     * 
     * @param string $orderString
     * @return Order[]
     */
    protected function orderToArray(string $orderString): array
    {
        $orders = explode("~", $orderString);

        if (empty($orders)) {
            return [];
        }

        $conditions = [];
        foreach ($orders as $order) {
            $oParts = explode("__", $order);
            if (count($oParts) !== 2) {
                continue;
            }

            $fieldName = $oParts[0];
            $direction = $oParts[1];

            $columnName = $this->getColumnName($fieldName);
            
            if (!$columnName) {
                $this->responder->E4005_OBJECT_NOT_FOUND("Column", $fieldName);
            }
            
            $conditions[] = new Order($columnName, $direction);
        }

        return $conditions;
    }

    /**
     * Check if current scheme contains property and return its corresponding column name
     * 
     * @param string $propertyName Property to get
     * @return string|null
     */
    private function getColumnName(string $propertyName): ?string
    {
        if (empty($this->getScheme()) || !is_array($this->getScheme()) || empty($propertyName)) {
            return null;
        }

        foreach ($this->getScheme() as $dbField) {
            /* @var $dbField Field */
            if ($dbField->getProperty() === $propertyName) {
                return $dbField->getColumn();
            }
        }

        return null;
    }

}
