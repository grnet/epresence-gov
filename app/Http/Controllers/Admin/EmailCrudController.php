<?php

namespace App\Http\Controllers\Admin;

use App\Document;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Backpack\CRUD\app\Http\Controllers\CrudController;

use App\Http\Requests\Admin\UpdateEmailRequest as StoreRequest;
use App\Http\Requests\Admin\UpdateEmailRequest as UpdateRequest;
use Illuminate\Support\Facades\Auth;
use Log;
use Storage;


class EmailCrudController extends CrudController
{
    public function setup()
    {

        $this->crud->setModel('App\Email');

        // $this->crud->removeAllButtons();

        $this->crud->setRoute("admin/emails");

        $this->crud->setEntityNameStrings('email', 'emails');

        $this->crud->denyAccess([ 'create','delete']);

//        $this->crud->denyAccess([ 'create','delete','update']);

        $this->crud->setColumns([

            [
                'name' => 'name',
                'label' => 'Name',
                'type' => 'text',
            ],
            [
                'name' => 'title',
                'label' => 'Subject',
                'type' => 'text',
            ],
            [
                'name' => 'sender_email',
                'label' => 'From',
                'type' => 'text',
            ],
        ]);

        $this->crud->addField([
            'name' => 'title',
            'label' => "Subject",
            'type' => 'text'
        ]);

        $this->crud->addField([
            'name' => 'sender_email',
            'label' => "From",
            'type' => 'text'
        ]);


    }

    public function store(StoreRequest $request)
    {
        return parent::storeCrud();
    }

    public function update(UpdateRequest $request)
    {
        return parent::updateCrud();
    }

    public function destroy($id)
    {
        $this->crud->hasAccessOrFail('delete');

        // get entry ID from Request (makes sure its the last ID for nested resources)
        $id = $this->crud->getCurrentEntryId() ?? $id;

        return $this->crud->delete($id);
    }


}
