<?php


namespace Rest\Controllers;


use Rest\Core\Controller;
use Rest\Core\Auth;
use Rest\Models\ResourceModel;

class ResourceController extends Controller
{

    protected $resourceName = 'resources';

    protected $validationRules = [
        'name' => ['string', 'required'],
        'controller' => ['string', 'required'],
    ];

    public function __construct(Auth $auth)
    {
        parent::__construct($auth);
        $this->model = new ResourceModel();
    }

}
