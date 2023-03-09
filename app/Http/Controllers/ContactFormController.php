<?php

namespace App\Http\Controllers;

use App\Mail\Contact;
use App\Person;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ContactFormController extends Controller {

    public function form()
    {
        if ( ! $user = auth()->user()) {
            $user = [
                'email' => '',
            ];
        }

        $subjects = $this->getSubjects();

        return view('pages.contact-form', compact('user', 'subjects'));
    }

    public function submit()
    {
        $data = request()->only([
            'subject_code',
            'name',
            'email_address',
            'message',
        ]);

        $subjects = $this->getSubjects();
        Validator::make($data, [
            'subject_code'  => ['string', 'required', Rule::in(Arr::pluck($subjects, 'code'))],
            'name'          => ['string', 'required'],
            'email_address' => ['email', 'required'],
            'message'       => ['string', 'required'],
        ])->validate();

        $data['subject'] = $this->byCode($data['subject_code'], $subjects)['label'] ?? null;

        Mail::later(now()->addSeconds(15), new Contact($data));

        session()->flash('status', __('pages.contacts.send_message_successfully'));

        return redirect()->route('contacts');
    }

    private function byCode(string $code, array $subjects): ?array
    {
        foreach ($subjects as $subject) {
            if ($subject['code'] === $code) {
                return $subject;
            }
        }

        return null;
    }

    private function getSubjects(): array
    {
        return [
            [
                'label' => __('contact.MP0001.label'),
                'code'  => 'MP0001',
            ],
            [
                'label' => __('contact.MP0002.label'),
                'code'  => 'MP0002',
            ],
            [
                'label' => __('contact.MP0003.label'),
                'code'  => 'MP0003',
            ],
            [
                'label' => __('contact.MP0004.label'),
                'code'  => 'MP0004',
            ],
            [
                'label' => __('contact.MP0005.label'),
                'code'  => 'MP0005',
            ],
            [
                'label' => __('contact.MP0006.label'),
                'code'  => 'MP0006',
            ],
            [
                'label' => __('contact.MP0007.label'),
                'code'  => 'MP0007',
            ],
            [
                'label' => __('contact.MP0008.label'),
                'code'  => 'MP0008',
            ],
            [
                'label' => __('contact.MP0000.label'),
                'code'  => 'MP0000',
            ],
        ];
    }
}
