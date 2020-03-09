<?php

namespace App\Exports;

use App\User;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;

class UsersExport implements FromQuery,WithHeadings,WithMapping
{
    use Exportable;

    static $data_arr=[];


    public function __construct($users_query)
    {
        $this->users_query = $users_query;
    }

    public function query()
    {
        return $this->users_query;
    }

    public function headings(): array
    {

        $heading_array = array(
            // 'Α/Α',
            'ID',
            'Ονοματεπώνυμο',
            'Μέιλ',
            'Επιβεβαιωμένος',
            'Εξωτερικός',
            'Τηλέφωνο',
            'Ρόλος',
            'Οργανισμός',
            'Τμήμα',
            'Ημ. δημιουργίας',
        );


        return $heading_array;
    }

    public function map($user): array
    {

        $state = $user->state == "sso" ? 'Όχι' : 'Ναι';
        $role = trans($user->roles()->first()->label);
        $confirmed = $user->confirmed == 0 ? 'Όχι' : 'Ναι';
        $institution = $user->institutions()->first() ? $user->institutions()->first()->title : null;
        $department = $user->departments()->first() ? $user->departments()->first()->title : null;

        $data_arr[] = [
            $user->id,
            $user->firstname . ' ' . $user->lastname,
            $user->email,
            $confirmed,
            $state,
            $user->telephone,
            $role,
            $institution,
            $department,
            $user->created_at->toDateTimeString(),

        ];


        return $data_arr;
    }


}