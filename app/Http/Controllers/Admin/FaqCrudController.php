<?php

namespace App\Http\Controllers\Admin;

use App\Document;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Backpack\CRUD\app\Http\Controllers\CrudController;

use App\Http\Requests\Admin\CreateFaqRequest as StoreRequest;
use App\Http\Requests\Admin\UpdateFaqRequest as UpdateRequest;
use Illuminate\Support\Facades\Auth;
use Log;
use Storage;


class FaqCrudController extends CrudController
{
    public function setup()
    {

        $this->crud->setModel('App\Faq');

        // $this->crud->removeAllButtons();

        $this->crud->setRoute("admin/faq");

        $this->crud->setEntityNameStrings('faq', 'faqs');

//        $this->crud->denyAccess([ 'create','delete','update']);

        $this->crud->setColumns([

            [
                'name' => 'id',
                'label' => 'ID',
                'type' => 'text',
            ],
            [
                'name' => 'order',
                'label' => 'Order',
                'type' => 'text',
            ],
            [
                'name' => 'el_question',
                'label' => 'Ερώτηση Ελληνικά',
                'type' => 'text',
            ],
            [
                'name' => 'en_question',
                'label' => 'Ερώτηση Αγγλικά',
                'type' => 'text',
            ],

            [
                'name' => 'el_answer',
                'label' => 'Απάντηση Ελληνικά',
                'type' => 'text',
            ],
            [
                'name' => 'en_answer',
                'label' => 'Απάντηση Αγγλικά',
                'type' => 'text',
            ],
            [
                'name' => 'active',
                'label' => 'Ενεργό',
                'type' => 'boolean',
            ]
        ]);

        $this->crud->addField([
            'name' => 'order',
            'label' => "Order",
            'type' => 'text'
        ]);

        $this->crud->addField([
            'name' => 'el_question',
            'label' => "Ερώτηση Ελληνικά",
            'type' => 'text'
        ]);

        $this->crud->addField([
            'name' => 'en_question',
            'label' => "Ερώτηση Αγγλικά",
            'type' => 'text'
        ]);

        $this->crud->addField([
            'name' => 'el_answer',
            'label' => "Απάντηση Ελληνικά",
            'type' => 'textarea'
        ]);

        $this->crud->addField([
            'name' => 'en_answer',
            'label' => "Απάντηση Αγγλικά",
            'type' => 'textarea'
        ]);

        $this->crud->addField([
            'name' => 'active',
            'label' => "Ενεργό",
            'type' => 'checkbox'
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
