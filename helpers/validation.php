<?php
function validateRequired($field, $value) {
    if (empty(trim($value))) {
        return "$field wajib diisi.";
    }
    return null;
}

function validateNumeric($field, $value) {
    if (!is_numeric($value)) {
        return "$field harus berupa angka.";
    }
    return null;
}

function validateNonNegative($field, $value) {
    if (!is_numeric($value) || $value < 0) {
        return "$field tidak boleh bernilai negatif.";
    }
    return null;
}
