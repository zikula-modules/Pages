'use strict';

function zikulaPagesValidateNoSpace(val) {
    var valStr;

    valStr = '' + val;

    return -1 === valStr.indexOf(' ');
}

/**
 * Runs special validation rules.
 */
function zikulaPagesExecuteCustomValidationConstraints(objectType, currentEntityId) {
}
