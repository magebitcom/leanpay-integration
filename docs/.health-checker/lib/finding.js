/**
 * @author    Magebit <info@magebit.com>
 * @copyright Copyright (c) Magebit, Ltd. (https://magebit.com)
 * @license   https://magebit.com/code-license
 */
'use strict';

const ERROR = 'error';
const WARNING = 'warning';

const error = (code, file, message) => ({ severity: ERROR, code, file, message });
const warning = (code, file, message) => ({ severity: WARNING, code, file, message });
const isError = (finding) => finding.severity === ERROR;

module.exports = { error, warning, isError };
