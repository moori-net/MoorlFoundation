<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

class TaxIdNumber extends Constraint
{
    public string $messageInvalidFormat = 'invalidFormat';
    public string $messageUnavailable = 'viesUnavailable';
    public string $messageInvalid = 'invalidTaxIdNumber';
    public string $messageSoapMissing = 'phpExtensionSoapMissing';

    public bool $online = true;
    public bool $failOnMissingSoap = false;
    public ?string $countryPropertyPath = null;
}
