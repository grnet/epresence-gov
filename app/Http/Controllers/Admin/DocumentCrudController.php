<?php

namespace App\Http\Controllers\Admin;

use App\Document;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Backpack\CRUD\app\Http\Controllers\CrudController;

use App\Http\Requests\Admin\CreateDocumentRequest as StoreRequest;
use App\Http\Requests\Admin\UpdateDocumentRequest as UpdateRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Log;
use Storage;


class DocumentCrudController extends CrudController
{
    public function setup()
    {

        $this->crud->setModel('App\Document');

        // $this->crud->removeAllButtons();

        $this->crud->setRoute("admin/documents");

        $this->crud->setEntityNameStrings('document', 'documents');

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
                'name' => "download_link_en",
                'label' => "Αρχείο Αγγλικά", // Table column heading
                'type' => "model_function",
                'function_name' => 'get_en_download_link', // the method in your Model
                'limit' => 500, // Limit the number of characters shown
            ],
            [
                'name' => "download_link_el",
                'label' => "Αρχείο Ελληνικά", // Table column heading
                'type' => "model_function",
                'function_name' => 'get_el_download_link', // the method in your Model
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

        $this->crud->addField([   // Upload
            'name' => 'en_file',
            'label' => 'Αρχείο Αγγλικά',
            'type' => 'upload',
            'upload' => true,
            'disk' => 'public' // if you store files in the /public folder, please ommit this; if you store them in /storage or S3, please specify it;
        ]);

        $this->crud->addField([
            'name' => 'en_file_url',
            'label' => "Εξωτερικός Σύνδεσμος Αγγλικά",
            'type' => 'text'
        ]);


        $this->crud->addField([   // Upload
            'name' => 'el_file',
            'label' => 'Αρχείο Ελληνικά',
            'type' => 'upload',
            'upload' => true,
            'disk' => 'public' // if you store files in the /public folder, please ommit this; if you store them in /storage or S3, please specify it;
        ]);


        $this->crud->addField([
            'name' => 'el_file_url',
            'label' => "Εξωτερικός Σύνδεσμος Ελληνικά",
            'type' => 'text'
        ]);

    }

    public function store(StoreRequest $request)
    {
        return parent::storeCrud();
    }

    public function update(UpdateRequest $request)
    {
        $input = $request->all();

        //Validate en_file field

        $document = Document::find($request->id);

        if(empty($request->en_file_url) && empty($request->el_file_url) && (!isset($document->el_file) || empty($document->el_file)) && (!isset($document->en_file) || empty($document->en_file))){
            $rules['en_file'] = 'required|file';
        }else{
            $rules['en_file'] = 'file|nullable';
        }

        if(empty($request->en_file_url) && empty($request->el_file_url) && (!isset($document->en_file) || empty($document->en_file)) && (!isset($document->el_file) || empty($document->el_file)) ){
            $rules['el_file'] = 'required|file';
        }else{
            $rules['el_file'] = 'file|nullable';
        }

        if(empty($request->el_file_url) && (!isset($document->el_file) || empty($document->el_file)) && (!isset($document->en_file) || empty($document->en_file)) ){
            $rules['en_file_url'] = 'required';
        }

        if(empty($request->en_file_url) && (!isset($document->el_file) || empty($document->el_file)) && (!isset($document->en_file) || empty($document->en_file))){
            $rules['el_file_url'] = 'required';
        }

        $validator = Validator::make($input,$rules);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        return parent::updateCrud();
    }

    public function destroy($id)
    {
        $this->crud->hasAccessOrFail('delete');

        // get entry ID from Request (makes sure its the last ID for nested resources)
        $id = $this->crud->getCurrentEntryId() ?? $id;

        $download = Document::find($id);

        if (Storage::disk('public')->has($download->en_file)) {
            Storage::disk('public')->delete($download->en_file);
        }


        if (Storage::disk('public')->has($download->el_file)) {
            Storage::disk('public')->delete($download->el_file);
        }

        return $this->crud->delete($id);
    }


}
