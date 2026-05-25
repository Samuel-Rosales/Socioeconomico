<?php
namespace App\Core;

class Validator {
    private $errors = [];

    public function validate(array $data, array $rules) {
        foreach ($rules as $field => $fieldRules) {
            $rulesArray = explode('|', $fieldRules);
            
            foreach ($rulesArray as $rule) {
                $this->applyRule($field, $data[$field] ?? null, $rule);
            }
        }
        return $this->errors;
    }

    private function applyRule($field, $value, $rule) {
        // Si el valor está vacío y NO es requerido, ignoramos el resto de reglas
        if ($rule !== 'required' && (is_null($value) || $value === '')) {
            return;
        }

        if ($rule === 'required' && (is_null($value) || $value === '')) {
            $this->errors[$field][] = "El campo $field es obligatorio.";
        }

        // Regla: Numérico (para IDs de catálogos)
        if ($rule === 'numeric' && !empty($value) && !is_numeric($value)) {
            $this->errors[$field][] = "El campo $field debe ser un número.";
        }

        // Regla: Email
        if ($rule === 'email' && !empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = "El formato de correo en $field no es válido.";
        }
    }
}