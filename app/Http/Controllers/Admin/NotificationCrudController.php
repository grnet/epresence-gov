<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Backpack\CRUD\app\Http\Controllers\CrudController;

use App\Http\Requests\Admin\UpdateNotificationRequest as StoreRequest;
use App\Http\Requests\Admin\CreateNotificationRequest as UpdateRequest;

class NotificationCrudController extends CrudController
{
    public function setup()
    {

        $this->crud->setModel('App\Notification');

        // $this->crud->removeAllButtons();

        $this->crud->setRoute("admin/notifications");

        $this->crud->setEntityNameStrings('notification', 'notifications');

//        $this->crud->denyAccess([ 'create','delete','update']);

        $this->crud->setColumns([

            [
                'name' => 'name',
                'label' => 'Αναγνωριστικό',
                'type' => 'text',
            ],

            [
                'name' => 'en_title',
                'label' => 'Τίτλος Αγγλικά',
                'type' => 'text',
            ],
            [
                'name' => 'el_title',
                'label' => 'Τίτλος Ελληνικά',
                'type' => 'text',
            ],

            [
                'name' => 'enabled',
                'label' => 'Ενεργό',
                'type' => 'boolean',
            ],
            [
                'name' => 'el_message',
                'label' => 'Κείμενο Ελληνικά',
                'type' => 'text',
            ],
            [
                'name' => 'en_message',
                'label' => 'Κείμενο Αγγλικά',
                'type' => 'text',
            ],

            [
                'name' => 'type',
                'label' => 'Τύπος',
                'type' => 'text',
            ],
        ]);

        $this->crud->addField([
            'name' => 'name',
            'label' => "Αναγνωριστικό",
            'type' => 'text',
            'attributes'=>[
//                "disabled"=>true
            ]
        ]);

        $this->crud->addField([
            'name' => 'en_title',
            'label' => "Τίτλος Αγγλικά",
            'type' => 'text'
        ]);

        $this->crud->addField([
            'name' => 'el_title',
            'label' => "Τίτλος Ελληνικά",
            'type' => 'text'
        ]);

        $this->crud->addField([
            'name' => 'enabled',
            'label' => "Ενεργό",
            'type' => 'checkbox'
        ]);

        $this->crud->addField([
            'name' => 'el_message',
            'label' => "Κείμενο Ελληνικά",
            'type' => 'wysiwyg'
        ]);

        $this->crud->addField([
            'name' => 'en_message',
            'label' => "Κείμενο Αγγλικά",
            'type' => 'wysiwyg'
        ]);
//
//
        $this->crud->addField([ // select_from_array
            'name' => 'type',
            'label' => "Type",
            'type' => 'select2_from_array',
            'options' => ["client"=> "Client", "global" => "Global"],
            'allows_null' => false,
            'default' => 'client',
            // 'allows_multiple' => true, // OPTIONAL; needs you to cast this to array in your model;
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
