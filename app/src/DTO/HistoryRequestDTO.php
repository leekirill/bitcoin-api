<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use App\Exception\ErrorMessages;

class HistoryRequestDTO
{
    #[Assert\Choice(
        choices: ['1h', '24h'],
        message: 'Invalid `range=` value. Only `1h` or `24h` are allowed.'
    )]
    public ?string $range = null;

    #[Assert\DateTime(
        format: \DateTimeInterface::ATOM,
        message: ErrorMessages::FROM['INVALID_FORMAT']
    )]
    public ?string $from = null;

    #[Assert\DateTime(
        format: \DateTimeInterface::ATOM,
        message: ErrorMessages::TO['INVALID_FORMAT']
    )]
    public ?string $to = null;

    #[Assert\Callback]
    public function validateParameters(ExecutionContextInterface $context)
    {
        if($this->range === null && $this->from === null && $this->to === null) {
            $context->buildViolation(ErrorMessages::BLANK_ALL)
                ->addViolation();
            return;
        }

        if($this->range !== null && ($this->from !== null || $this->to !== null)) {
            $context->buildViolation(ErrorMessages::SAME_TIME)
                ->addViolation();
            return;
        }

        if ($this->from !== null || $this->to !== null) {
            if ($this->from === null || trim($this->from) === '') {
                $context->buildViolation(ErrorMessages::FROM['BLANK'])
                    ->atPath('from')
                    ->addViolation();
            }
            if ($this->to === null || trim($this->to) === '') {
                $context->buildViolation(ErrorMessages::TO['BLANK'])
                    ->atPath('to')
                    ->addViolation();
            }
            return;
        }

        if ($this->range === null || trim($this->range) === '') {
            $context->buildViolation(ErrorMessages::RANGE['INVALID_RANGE'])
                ->atPath('range')
                ->addViolation();
        }
    }
}
