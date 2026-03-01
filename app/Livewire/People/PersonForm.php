<?php

namespace App\Livewire\People;

use App\Domain\People\Actions\CreatePerson;
use App\Domain\People\Actions\UpdatePerson;
use App\Models\Person;
use App\Support\Enums\Gender;
use App\Support\Enums\HowFoundUs;
use App\Support\Enums\MaritalStatus;
use App\Support\Enums\PersonStatus;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class PersonForm extends Component
{
    public bool $show = false;
    public ?int $personId = null;

    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $phone = '';
    public string $birth_date = '';
    public string $gender = '';
    public string $marital_status = '';
    public string $status = '';
    public string $address_line1 = '';
    public string $city = '';
    public string $state = '';
    public string $first_visit_date = '';
    public string $how_found_us = '';
    public string $notes_pastoral = '';

    protected function rules(): array
    {
        return [
            'first_name'       => 'required|string|max:100',
            'last_name'        => 'required|string|max:100',
            'email'            => 'nullable|email|max:150',
            'phone'            => 'nullable|string|max:20',
            'birth_date'       => 'nullable|date',
            'gender'           => 'nullable|in:' . implode(',', array_column(Gender::cases(), 'value')),
            'marital_status'   => 'nullable|in:' . implode(',', array_column(MaritalStatus::cases(), 'value')),
            'status'           => 'required|in:' . implode(',', array_column(PersonStatus::cases(), 'value')),
            'address_line1'    => 'nullable|string|max:255',
            'city'             => 'nullable|string|max:100',
            'state'            => 'nullable|string|max:100',
            'first_visit_date' => 'nullable|date',
            'how_found_us'     => 'nullable|in:' . implode(',', array_column(HowFoundUs::cases(), 'value')),
            'notes_pastoral'   => 'nullable|string',
        ];
    }

    public function mount(): void
    {
        $this->status = PersonStatus::Visitor->value;
    }

    public function listeners(): array
    {
        return ['open-person-form' => 'open'];
    }

    protected function getListeners(): array
    {
        return ['open-person-form' => 'open'];
    }

    public function open(?int $id = null): void
    {
        $this->resetValidation();
        $this->reset([
            'first_name', 'last_name', 'email', 'phone', 'birth_date',
            'gender', 'marital_status', 'address_line1', 'city', 'state',
            'first_visit_date', 'how_found_us', 'notes_pastoral',
        ]);
        $this->status = PersonStatus::Visitor->value;

        $this->personId = $id;

        if ($id) {
            $person = Person::findOrFail($id);
            $this->first_name       = $person->first_name;
            $this->last_name        = $person->last_name;
            $this->email            = $person->email ?? '';
            $this->phone            = $person->phone ?? '';
            $this->birth_date       = $person->birth_date?->format('Y-m-d') ?? '';
            $this->gender           = $person->gender?->value ?? '';
            $this->marital_status   = $person->marital_status?->value ?? '';
            $this->status           = $person->status?->value ?? PersonStatus::Visitor->value;
            $this->address_line1    = $person->address_line1 ?? '';
            $this->city             = $person->city ?? '';
            $this->state            = $person->state ?? '';
            $this->first_visit_date = $person->first_visit_date?->format('Y-m-d') ?? '';
            $this->how_found_us     = $person->how_found_us?->value ?? '';
            $this->notes_pastoral   = $person->notes_pastoral ?? '';
        }

        $this->show = true;
    }

    public function save(): void
    {
        if ($this->personId) {
            abort_unless(Gate::allows('people.update'), 403);
        } else {
            abort_unless(Gate::allows('people.create'), 403);
        }

        $data = $this->validate();

        // Clean nullable empty strings
        $data = array_map(fn ($v) => $v === '' ? null : $v, $data);

        if ($this->personId) {
            $person = Person::findOrFail($this->personId);
            (new UpdatePerson())->handle($person, $data);
            $message = 'Persona actualizada correctamente.';
        } else {
            (new CreatePerson())->handle($data);
            $message = 'Persona creada correctamente.';
        }

        $this->show = false;
        $this->dispatch('person-saved');
        session()->flash('success', $message);
    }

    public function close(): void
    {
        $this->show = false;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.people.form', [
            'genders'        => Gender::cases(),
            'maritalStatuses' => MaritalStatus::cases(),
            'statuses'       => PersonStatus::cases(),
            'howFoundUs'     => HowFoundUs::cases(),
        ]);
    }
}
