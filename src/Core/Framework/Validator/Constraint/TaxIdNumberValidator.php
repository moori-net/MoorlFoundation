<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\Validator\Constraint;

use MoorlFoundation\Core\Framework\Vies\ViesClientInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class TaxIdNumberValidator extends ConstraintValidator
{
    public function __construct(
        private readonly ViesClientInterface $vies
    ) {}

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof TaxIdNumber) {
            throw new UnexpectedTypeException($constraint, TaxIdNumber::class);
        }
        if ($value === null || $value === '') {
            return;
        }
        if (!\is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $vat = \preg_replace('/[^a-zA-Z0-9]/', '', $value);

        if (!\preg_match('/^[A-Z]{2}[A-Z0-9]{2,12}$/i', $vat)) {
            $this->context->buildViolation($constraint->messageInvalidFormat)->addViolation();
            return;
        }

        $cc = \strtoupper(\substr($vat, 0, 2));
        $num = \substr($vat, 2);
        if ($constraint->countryPropertyPath) {
            $accessor = PropertyAccess::createPropertyAccessor();
            $rootCC = $accessor->getValue($this->context->getRoot(), $constraint->countryPropertyPath);
            if (\is_string($rootCC) && $rootCC !== '') {
                $cc = \strtoupper($rootCC);
                if (!\preg_match('/^[A-Z]{2}/i', $value)) {
                    $num = $vat;
                }
            }
        }

        if ($constraint->online) {
            $soapAvailable = \extension_loaded('soap') && \class_exists(\SoapClient::class, false);

            if (!$soapAvailable) {
                if ($constraint->failOnMissingSoap) {
                    $this->context->buildViolation($constraint->messageSoapMissing)->addViolation();
                }
                return;
            }

            try {
                $ok = $this->vies->checkVat($cc, $num);
            } catch (\Throwable $e) {
                $this->context->buildViolation($constraint->messageUnavailable)->addViolation();
                return;
            }

            if (!$ok) {
                $this->context->buildViolation($constraint->messageInvalid)->addViolation();
            }
        }
    }
}
