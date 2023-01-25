<?php


namespace Rest\Core;

class Controller
{
    protected ErrorBag $errorBag;
    protected Auth $auth;
    protected Validator $validator;
    protected Logger $logger;
    protected Model $model;
    protected $resourceName = '';
    protected $validationRules = [];
    protected $whereData = [];
    protected $limit;
    protected $offset;
    protected $order;
    protected $includeItems = true;

    public function __construct(Auth $auth)
    {
        $this->errorBag = ErrorBag::getInstance();
        $this->auth = $auth;
        $this->validator = new Validator();
        $this->logger = new Logger();
        $this->whereData = $_GET;
        list($this->limit, $this->offset, $this->order, $this->includeItems) = $this->setDefaultGetParams();

        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            http_response_code(500);
            $this->logger->logError($errno, $errstr, $errfile, $errline);
            $errorString = "$errfile: $errline, $errstr";
            $this->errorBag->addError($errorString);
        });
    }

    public function index()
    {
        $results = $this->model->read($this->whereData, $this->limit, $this->offset, $this->order);
        return $results;
    }

    public function readById($id)
    {
        $results = $this->model->readById($id);
        return $results;
    }

    public function create()
    {
        $validation = $this->validator->validateData($_POST, $this->validationRules);
        if ($validation) {
            $result = $this->model->create($_POST);
            if ($result) {
                http_response_code(201);
                return $result;
            }
        }
        return [];
    }

    public function update($id)
    {
        $results = $this->model->update($id, $_POST);
        return $results;
    }

    public function delete($id)
    {
        $results = $this->model->delete($id);
        return $results;
    }

    protected function setDefaultGetParams()
    {
        $limit = false;
        if (isset($_GET['limit'])) {
            $limit = $_GET['limit'];
        }
        $offset = false;
        if (isset($_GET['offset'])) {
            $offset = $_GET['offset'];
        }
        $order = false;
        if (isset($_GET['order'])) {
            $order = $_GET['order'];
        }
        $includeItems = true;
        if (isset($_GET['include_items'])) {
            $includeItems = $_GET['include_items'];
        }
        return [$limit, $offset, $order, $includeItems];
    }
}
