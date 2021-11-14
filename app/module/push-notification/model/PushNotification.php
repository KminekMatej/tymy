<?php
/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */
namespace Tymy\Module\PushNotification\Model;

use Tymy\Module\Core\Model\BaseModel;

/**
 * Description of PushNotification
 *
 * @author kminekmatej, 14. 11. 2021, 21:29:25
 */
class PushNotification implements \JsonSerializable
{

    private string $message;
    private string $module;
    private ?int $modelId;
    private ?BaseModel $model;

    public function __construct(?string $message, string $module, ?int $modelId, ?BaseModel $model)
    {
        $this->message = $message;
        $this->module = $module;
        $this->modelId = $modelId;
        $this->model = $model;
    }

    public function jsonSerialize(): mixed
    {
        $array = [
            "message" => $this->message,
            "module" => $this->module,
        ];

        if (isset($this->modelId)) {
            $array["modelId"] = $this->modelId;
        }

        if (isset($this->model)) {
            $array["model"] = $this->model->jsonSerialize();
        }

        return $array;
    }
}
