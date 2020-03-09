<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class StatisticsRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
         return [
           'start' => 'required_with:select_period|date_format:d-m-Y|before:tomorrow|before:end',
           'end' => 'required_with:select_period|date_format:d-m-Y|before:tomorrow',
		   'to' => 'required_with:period|date_format:d-m-Y|before:tomorrow',
           'period' => 'required_with:to|in:3,6'
        ];
    }
	
	public function messages()
    {
        return [
            'start.required_with' => 'Η ημερομηνία έναρξης δεν μπορεί να είναι κενή!',
            'start.date_format' => 'Η ημερομηνία έναρξης πρέπει να έχει τη μορφή ημέρα-μήνας-έτος',
            'start.before' => 'Η ημερομηνία έναρξης πρέπει να είναι πριν από την αυριανή και/ή πριν την ημερομηνία λήξης',
			'end.required_with' => 'Η ημερομηνία λήξης δεν μπορεί να είναι κενή!',
			'end.date_format' => 'Η ημερομηνία λήξης πρέπει να έχει τη μορφή ημέρα-μήνας-έτος',
			'end.before' => 'Η ημερομηνία λήξης πρέπει να είναι πριν από την αυριανή',
			'to.required_with' => 'Η ημερομηνία δεν μπορεί να είναι κενή!',
            'to.date_format' => 'Η ημερομηνία πρέπει να έχει τη μορφή ημέρα-μήνας-έτος',
            'to.before' => 'Η ημερομηνία πρέπει να είναι πριν από την αυριανή ημερομηνία',
            'period.required_with' => 'Το διάστημα δεν μπορεί να είναι κενό!',
            'period.in' => 'Το διάστημα μπορεί να είναι μόνο Τρίμηνο ή Εξάμηνο',
        ];
    }
}
