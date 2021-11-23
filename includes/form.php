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
        protected ?closure $sanitizer = NULL, // default: function (string $input): mixed {return input;}
        protected mixed $defaultContent = ''
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
        $content = $_POST[$this->name] ?? $this->defaultContent;
        if ($this->sanitizer != NULL)
            return ($this->sanitizer)($content);
        else
            return $content;
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

class Textareafield extends Field {
    private int $rows = 3;

    /* Default Field constructor extended by a rows element */
    public function __construct (mixed ...$args) {
        if (isset($args['rows'])) {
            $this->rows = $args['rows'];
            unset($args['rows']);
        }
        parent::__construct(...$args);
    }

    public function getFieldGroup (bool $markValidity): string {
        $isValid = $this->isValid();
        return '<div class="form-group' . ($this->groupCss !== NULL ? ' ' . $this->groupCss : '') . '">
        <label for="' . $this->name . '">' . $this->fulltext 
        . ($this->required ? ' <abbr style="color: red" title="Pflichtfeld">*</abbr>' : '')
        . '</label><textarea rows="' . $this->rows . '" class="form-control'
        . ($markValidity ? ($isValid ? ' is-valid' : ' is-invalid') : '')
        . '" name="' . $this->name . '" placeholder="' . $this->fulltext . '">'
        . $this->getFieldContent() . '</textarea>'
        . ($markValidity && !$isValid && $this->invalidMsg !== NULL ? '<div class="invalid-feedback">'
        . $this->invalidMsg . '</div>' : '')
        . ($this->subtext !== NULL ? '<small class="form-text text-muted">' . $this->subtext . '</small>' : '')
        . '</div>';
    }
}

class Boxfield extends Field {
    private array $boxes = array();

    public function __construct(...$args) {
        if (!isset($args['defaultContent']))
            $args['defaultContent'] = array();
        parent::__construct(...$args);
    }

    // type "checkbox" or "radio"
    public function addBox (string $type, string $value, string $description): Boxfield {
        $this->boxes[] = ['type' => $type, 'value' => $value, 'description' => $description];
        return $this;
    }

    public function getFieldGroup (bool $markValidity): string {
        $isValid = $this->isValid();
        $content = $this->getFieldContent();
        $returner = '<div class="form-group' . ($this->groupCss !== NULL ? ' ' . $this->groupCss : '') . '">
        <label for="' . $this->name . '">' . $this->fulltext 
        . ($this->required ? ' <abbr style="color: red" title="Pflichtfeld">*</abbr>' : '')
        . '</label>';
        foreach ($this->boxes as $i => $box) {
            $returner .= '<div class="form-check"><input type="' . $box['type']
            . '" name="' . $this->name . '[]" value="' . $box['value']
            . '" class="form-check-input" id="' . $this->name . '-' . $i . '"'
            . (is_array($content) && in_array($box['value'], $content) ? ' checked' : '')
            . '><label class="form-check-label" for="' . $this->name
            . '-' . $i . '">' . $box['description'] . '</label></div>';
        }
        $returner .= ($markValidity && !$isValid && $this->invalidMsg !== NULL ? '<div class="invalid-feedback">'
        . $this->invalidMsg . '</div>' : '')
        . ($this->subtext !== NULL ? '<small class="form-text text-muted">' . $this->subtext . '</small>' : '')
        . '</div>';
        return $returner;
    }
}

class Hiddenfield extends Field {
    public function getFieldGroup (bool $markValidity): string {
        return '<input type="hidden" name="' . $this->name
        . '" value="' . $this->getFieldContent() . '">';
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