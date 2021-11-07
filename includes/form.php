<?php
if (!defined('INVDB'))
    die('No access');


class Form {
    private array $fields = [];

    // adds a new field to a form validator
    public function addField (Field $field): void {
        $this->fields[$field->getName()] = $field;
    }

    // returns the formatted and sanitized field
    public function getFieldContent (string $name): mixed {
        return $this->fields[$name]->getFieldContent();
    }

    // gets the HTML form element
    public function getFieldGroup (string $name, bool $markValidity = false): string {
        return $this->fields[$name]->getFieldGroup($markValidity);
    }

    // checks, if all form elements token were sent (if required)
    public function wasSent (): bool {
        foreach ($this->fields as $field) {
            if (!$field->wasSent())
                return false;
        }
        return true;
    }

    // returns true, if the form were successfully validated
    public function isValid (): bool {
        foreach ($this->fields as $field) {
            if (!$field->isValid())
                return false;
        }
        return true;
    }
}



abstract class Field {
    public function __construct (
        protected string $name,
        protected string $fulltext,
        protected bool $required,
        protected ?string $subtext = NULL,
        protected ?string $groupCss = NULL,
        protected ?closure $validator = NULL, // default: function (string $input): bool {return true;}
        protected ?string $invalidMsg = NULL,
        protected ?closure $sanitizer = NULL // default: function (string $input): mixed {return input;} 
    ) {}

    public function getName(): string {
        return $this->name;
    }

    abstract public function getFieldGroup (bool $markValidity): string;

    public function wasSent (): bool {
        return $this->required ? isset($_POST[$this->name]) : true;
    }

    public function isValid (): bool {
        $content = $_POST[$this->name] ?? '';
        if ($this->required && empty($content))
            return false;
        if (!empty($content) && $this->validator != NULL && !($this->validator)($content))
            return false;
        return true;
    }

    public function getFieldContent (): mixed {
        if ($this->sanitizer != NULL)
            return ($this->sanitizer)($_POST[$this->name] ?? '');
        else
            return $_POST[$this->name] ?? '';
    }
}


class Textfield extends Field {
    public function getFieldGroup (bool $markValidity): string {
        $isValid = $this->isValid();
        return '<div class="form-group' . ($this->groupCss !== NULL ? ' ' . $this->groupCss : '')  . '">
        <label for="' . $this->name . '">' . $this->fulltext 
        . ($this->required ? ' <abbr style="color: red" title="Pflichtfeld">*</abbr>' : '')
        . '</label><input type="text" value="' . $this->getFieldContent() . '" class="form-control'
        . ($markValidity ? ($isValid ? ' is-valid' : ' is-invalid') : '')
        . '" name="' . $this->name . '" placeholder="' . $this->fulltext . '">'
        . ($markValidity && !$isValid && $this->invalidMsg !== NULL ? '<div class="invalid-feedback">'
        . $this->invalidMsg . '</div>' : '')
        . ($this->subtext !== NULL ? '<small class="form-text text-muted">' . $this->subtext . '</small>' : '')
        . '</div>';
    }
}

class Mailfield extends Field {
    public function getFieldGroup (bool $markValidity): string {
        $isValid = $this->isValid();
        return '<div class="form-group' . ($this->groupCss !== NULL ? ' ' . $this->groupCss : '') . '">
        <label for="' . $this->name . '">' . $this->fulltext 
        . ($this->required ? ' <abbr style="color: red" title="Pflichtfeld">*</abbr>' : '')
        . '</label><input type="email" value="' . $this->getFieldContent() . '" class="form-control'
        . ($markValidity ? ($isValid ? ' is-valid' : ' is-invalid') : '')
        . '" name="' . $this->name . '" placeholder="' . $this->fulltext . '">'
        . ($markValidity && !$isValid && $this->invalidMsg !== NULL ? '<div class="invalid-feedback">'
        . $this->invalidMsg . '</div>' : '')
        . ($this->subtext !== NULL ? '<small class="form-text text-muted">' . $this->subtext . '</small>' : '')
        . '</div>';
    }
}

class Passwordfield extends Field {
    public function getFieldGroup (bool $markValidity): string {
        $isValid = $this->isValid();
        return '<div class="form-group' . ($this->groupCss !== NULL ? ' ' . $this->groupCss : '') . '">
        <label for="' . $this->name . '">' . $this->fulltext 
        . ($this->required ? ' <abbr style="color: red" title="Pflichtfeld">*</abbr>' : '')
        . '</label><input type="password" class="form-control'
        . ($markValidity ? ($isValid ? ' is-valid' : ' is-invalid') : '')
        . '" name="' . $this->name . '" placeholder="' . $this->fulltext . '">'
        . ($markValidity && !$isValid && $this->invalidMsg !== NULL ? '<div class="invalid-feedback">'
        . $this->invalidMsg . '</div>' : '')
        . ($this->subtext !== NULL ? '<small class="form-text text-muted">' . $this->subtext . '</small>' : '')
        . '</div>';
    }
}

class CSRFfield extends Field {
    function __construct () {
        parent::__construct('CSRF', '', true, validator: function (string $input): bool {
            global $SESS;
            return ($_POST['CSRF'] ?? '') == $SESS['CSRF'];
        });
    }
    public function getFieldGroup (bool $markValidity): string {
        global $SESS;
        return '<input type="hidden" name="CSRF" value="'.$SESS['CSRF'].'">';
    }
}
?>