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
use Tracy\Debugger;
use Tymy\Module\Core\Exception\DBException;
use Tymy\Module\Core\Factory\ManagerFactory;
use Tymy\Module\Core\Helper\DateHelper;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Core\Model\Field;
use Tymy\Module\Core\Model\Filter;
use Tymy\Module\Core\Model\Order;
use Tymy\Module\Team\Model\Team;
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

    /** @var int[] */
    private array $allIdList = [];
    /** @var int[] */
    private array $lastIdList = [];
    protected ?string $idCol = "id"; //default id column name

    public function __construct(ManagerFactory $managerFactory)
    {
        $this->mainDatabase = $managerFactory->mainDatabase;
        $this->teamDatabase = $managerFactory->teamDatabase;
        $this->database = $this->teamDatabase;
        $this->teamSysName = $managerFactory->teamSysName;
        $this->responder = $managerFactory->responder;
        $this->user = $managerFactory->user;
    }

    /**
     * Exception safe global team log
     */
    public static function logg(Team $team, string $log): void
    {
        try {
            Debugger::log("[{$team->getSysName()}] $log", "../../../teamlog");
        } catch (Exception $exc) {
            Debugger::log($exc->getMessage());
        }
    }

    /** @return Field[] */
    abstract protected function getScheme(): array;

    abstract public function create(array $data, ?int $resourceId = null): BaseModel;

    abstract public function read(int $resourceId, ?int $subResourceId = null): BaseModel;

    abstract public function update(array $data, int $resourceId, ?int $subResourceId = null): BaseModel;

    abstract public function delete(int $resourceId, ?int $subResourceId = null): int;

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
     * @param BaseModel $record
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
     * @param IRow|null $row
     * @return BaseModel|null
     */
    public function map(?IRow $row, bool $force = false): ?BaseModel
    {
        if ($row === null) {
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
     */
    public function checkInputs(array $array): void
    {
        foreach ($this->getScheme() as $field) {
            if (!$field->getChangeable()) {   //when field is not changeable, its only logical they are not being sent
                continue;
            }

            $input = $field->getProperty();
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
     * @return ActiveRow $row
     */
    public function getRow(int $id): ?ActiveRow
    {
        return $this->database->table($this->getTable())->where("id", $id)->fetch();
    }

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
    public function getList(?array $idList = null, string $idField = "id", ?int $limit = null, ?int $offset = null, ?string $order = null): array
    {
        $rows = $this->database->table($this->getTable());
        if ($order !== null) {
            $rows->order($order);
        }
        if ($idList !== null) {
            $rows->where($idField, $idList);
        }

        if (is_int($limit) && is_int($offset)) {
            $rows->limit($limit, $offset);
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
     * Check if record, specified by ID, exists - performs a quick db check
     */
    public function exists(int $id, string $table = null): bool
    {
        $table = $table ?: $this->getTable();
        return $this->database->table($table)->where("id", $id)->count("id") > 0;
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
    protected function updateRecord(string $table, int $id, array $updates, string $idColumn = "id")
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
     * @return ActiveRow
     * @throws Exception
     */
    protected function createRecord(string $table, array $inserts, string $idColumn = "id")
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
     * @param string $idColumn
     * @return int
     */
    protected function deleteRecord(int $id, ?string $table = null, string $idColumn = "id")
    {
        try {
            $deleted = $this->database->table($table ?: $this->getTable())->where($idColumn, $id)->delete();
        } catch (PDOException $exc) {
            $e = DBException::from($exc, DBException::TYPE_DELETE);
            $e->withIds($this->database->table($e->fkTable)->where($e->failingField, $id)->fetchPairs(null, "id"));
            throw $e;
        }

        if ($deleted === 0) {
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
        $updates = $this->composeUpdateArray($array);

        return $this->updateRecord($this->getTable(), $id, $updates);
    }

    /**
     * Creates new entity record and returns it on success
     * @param array $array Values to create
     * @return ActiveRow Created row
     * @throws Exception
     */
    public function createByArray(array $array)
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
    public function composeInsertArray(array $array)
    {
        $inserts = [];

        foreach ($this->getScheme() as $field) {
            assert($field instanceof Field);
            if ($field->getMandatory() && !array_key_exists($field->getProperty(), $array)) {
                $this->responder->E4013_MISSING_INPUT($field->getProperty());
            }

            $value = $array[$field->getProperty()] ?? null;

            if (!$field->getChangeable()) {
                if (in_array($field->getColumn(), ["user_id", "usr_cre", "created_user_id"]) && !empty($this->user)) {
                    $value = $this->user->getId();
                } elseif (in_array($field->getColumn(), ["dat_cre", "insert_date", "created"])) {
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

            if (!empty($value)) {
                $this->sanitizeValue($field, $value);
            }

            $inserts[$field->getColumn()] = $value;
        }

        return $inserts;
    }

    /**
     * Use mapper data to compose array of database fields to update from specified input array
     * @param array $array Input data (usually $this->requestData)
     * @param array $scheme Scheme upon which wo work (optional)
     * @return array Update output
     */
    protected function composeUpdateArray(array $array, ?array $scheme = null): array
    {
        $updates = $additionalUpdates = [];
        $sch = $scheme ?: $this->getScheme();

        foreach ($sch as $field) {
            assert($field instanceof Field);
            if (!array_key_exists($field->getProperty(), $array)) { //this field is not mentioned in update data, fill it only if its update field
                if ($field->getColumn() == "updated_user_id" && !empty($this->user)) {
                    $additionalUpdates[$field->getColumn()] = $this->user->getId();
                }
                if ($field->getColumn() == "updated") {
                    $additionalUpdates[$field->getColumn()] = new DateTime();
                }
                continue;
            }

            $value = $array[$field->getProperty()];
            if (!$field->getChangeable()) {
                continue;
            }

            if ($field->getNonempty() && $value === null) {
                $this->responder->E4014_EMPTY_INPUT($field->getProperty());
            }

            $this->sanitizeValue($field, $value);

            $updates[$field->getColumn()] = $value;
        }

        if (!empty($updates) && !empty($additionalUpdates)) { //if there has been some fileds updated, merge also fields updated holding update informations
            $updates += $additionalUpdates;
        }

        return $updates;
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
     * @param int $recordId Id of record to read (optional)
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
     * @param BaseModel $model
     * @param int $userId
     * @return void
     */
    protected function metaMap(BaseModel &$model, int $userId = null): void
    {
        $model->setHasMeta(false); //when this function is not overloaded, set hasMeta to false - to make always detectible if this entity needs some meta mapping purposes
    }

    /**
     * Get user ids allowed to read given id
     *
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

    protected function respondUnauthorized(?string $message = null)
    {
        $this->responder->E401_UNAUTHORIZED($message);
    }

    protected function respondForbidden(?string $message = null)
    {
        $this->responder->E403_FORBIDDEN($message ?? "Nedostatečná práva");
    }

    protected function respondNotFound(?string $module = null, ?int $id = null): never
    {
        $this->responder->E404_NOT_FOUND($module, $id);
    }

    protected function respondNotAllowed()
    {
        $this->responder->E405_METHOD_NOT_ALLOWED();
    }

    /**
     * Load record using id. Responds with 404 if not found
     *
     * @return BaseModel
     */
    protected function loadRecord(int $recordId, ?BaseManager $manager = null)
    {
        $record = $manager !== null ? $manager->getById($recordId) : $this->getById($recordId);

        if (!$record instanceof BaseModel) {
            $this->respondNotFound();
        }

        return $record;
    }

    /**
     * Iterate through filterString, parse out all filters and return from them the array or Filter objects for further processing
     *
     * @return Filter[]
     */
    protected function filterToArray(string $filterString): array
    {
        if (empty($filterString)) {
            return [];
        }

        $filters = explode("~", $filterString);
        $fParts = null;

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
     * @return Order[]
     */
    protected function orderToArray(string $orderString): array
    {
        if (empty($orderString)) {
            return [];
        }

        $orders = explode("~", $orderString);

        $conditions = [];
        foreach ($orders as $order) {
            $oParts = explode("__", $order);
            if (count($oParts) == 1) {
                //only field name is specified, for instance "startTime" - add DESC as default
                $oParts[1] = "DESC";
            } elseif (count($oParts) !== 2) {
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
     */
    private function getColumnName(string $propertyName): ?string
    {
        if (empty($this->getScheme()) || !is_array($this->getScheme()) || empty($propertyName)) {
            return null;
        }

        foreach ($this->getScheme() as $dbField) {
            assert($dbField instanceof Field);
            if ($dbField->getProperty() === $propertyName) {
                return $dbField->getColumn();
            }
        }

        return null;
    }

    public function getLastIdList(): array
    {
        return $this->lastIdList;
    }

    /**
     * Change all fields in input $data array to proper bool value (if the fields are correctly specified)
     */
    protected function toBoolData(array &$data, array $fields): void
    {
        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = $this->toBool($data[$field]);
            }
        }
    }

    /**
     * Retype various boolean stored variables like YES, ANO etc. to proper boolean value
     */
    protected function toBool(mixed $value): bool
    {
        if (is_string($value)) {
            return in_array(strtolower($value), ["yes", "true", "ano"]);
        }

        return (bool) $value;
    }

    /**
     * Sanitize value from Field specification
     * @param Field $field
     * @param mixed $value
     * @return void
     */
    private function sanitizeValue(Field $field, mixed &$value): void
    {
        switch ($field->getType()) {
            case Field::TYPE_DATETIME:
                $value = !empty($value) ? DateHelper::createLc($value) : null; //format DateTime only if its not null or empty
                break;
            case Field::TYPE_FLOAT:
                if (is_numeric($value)) {
                    $value = round(floatval($value), 6);
                } elseif (is_null($value)) {
                    $value = null;
                } else {    //float value not supported, empty string or null - simply skip it then
                    return;
                }
                break;
        }
    }
}
