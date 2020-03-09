<?php

namespace App\Http\Controllers\Admin;

use App\Download;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Backpack\CRUD\app\Http\Controllers\CrudController;

use App\Http\Requests\Admin\CreateVideoRequest as StoreRequest;
use App\Http\Requests\Admin\UpdateVideoRequest as UpdateRequest;
use Illuminate\Support\Facades\Auth;
use Log;
use Storage;


class VideoCrudController extends CrudController
{
    public function setup()
    {

        $this->crud->setModel('App\Video');

        // $this->crud->removeAllButtons();

        $this->crud->setRoute("admin/videos");

        $this->crud->setEntityNameStrings('video', 'videos');

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
                'name' => 'youtube_video_id',
                'label' => 'Video id',
                'type' => 'text',
            ],


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
            'name' => 'youtube_video_id',
            'label' => "Κωδικός Youtube video",
            'type' => 'text',
            'hint' => 'Εδώ συμπληρώστε μόνο το ID του youtube video.( https://www.youtube.com/watch?v={ID} ) ',
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
