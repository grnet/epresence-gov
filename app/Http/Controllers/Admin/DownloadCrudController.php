<?php

namespace App\Http\Controllers\Admin;

use App\Download;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Backpack\CRUD\app\Http\Controllers\CrudController;

use App\Http\Requests\Admin\CreateDownloadRequest  as StoreRequest;
use App\Http\Requests\Admin\UpdateDownloadRequest as UpdateRequest;
use Illuminate\Support\Facades\Auth;
use Log;
use Storage;


class DownloadCrudController extends CrudController
{
    public function setup()
    {

        $this->crud->setModel('App\Download');

        // $this->crud->removeAllButtons();

        $this->crud->setRoute("admin/downloads");

        $this->crud->setEntityNameStrings('download', 'downloads');

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
                'name' => 'title_el',
                'label' => 'Τίτλος Ελληνικά',
                'type' => 'text',
            ],
            [
                'name' => 'title_en',
                'label' => 'Τίτλος Αγγλικά',
                'type' => 'text',
            ],
            [
                'name' => 'description_el',
                'label' => 'Περιγραφή Ελληνικά',
                'type' => 'text',
            ],
            [
                'name' => 'description_en',
                'label' => 'Περιγραφή Αγγλικά',
                'type' => 'text',
            ],
            [
                'name' => "download_link",
                'label' => "Αρχείο", // Table column heading
                'type' => "model_function",
                'function_name' => 'get_download_link', // the method in your Model
                'limit' => 500, // Limit the number of characters shown
            ]
        ]);

        $this->crud->addField([
            'name' => 'order',
            'label' => "Order",
            'type' => 'text'
        ]);

        $this->crud->addField([
            'name' => 'title_el',
            'label' => "Τίτλος Ελληνικά",
            'type' => 'text'
        ]);

        $this->crud->addField([
            'name' => 'title_en',
            'label' => "Τίτλος Αγγλικά",
            'type' => 'text'
        ]);

        $this->crud->addField([
            'name' => 'description_el',
            'label' => "Περιγραφή Ελληνικά",
            'type' => 'text'
        ]);

        $this->crud->addField([
            'name' => 'description_en',
            'label' => "Περιγραφή Αγγλικά",
            'type' => 'text'
        ]);

        $this->crud->addField([   // Upload
            'name' => 'file_path',
            'label' => 'File URL',
            'type' => 'text',
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

        $download = Download::find($id);

        if (Storage::disk('public')->has($download->file_path)) {
            Storage::disk('public')->delete($download->file_path);
        }

        return $this->crud->delete($id);
    }


}
