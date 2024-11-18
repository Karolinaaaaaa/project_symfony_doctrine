<?php

namespace App\Core\Invoice\Application\Command\CreateInvoice;

use App\Core\Invoice\Domain\Exception\InvoiceException;
use App\Core\Invoice\Domain\Invoice;
use App\Core\Invoice\Domain\Repository\InvoiceRepositoryInterface;
use App\Core\User\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CreateInvoiceHandler
{
    public function __construct(
        private readonly InvoiceRepositoryInterface $invoiceRepository,
        private readonly UserRepositoryInterface $userRepository
    ) {}

    public function __invoke(CreateInvoiceCommand $command): void
    {
        if ($command->amount <= 0) {
            throw new InvoiceException('Kwota faktury musi być większa od 0.');
        }

        $user = $this->userRepository->getByEmail($command->email);

        if (!$user->isActive()) {
            throw new \DomainException('Faktury mogą być tworzone tylko dla aktywnych użytkowników.');
        }

        $invoice = new Invoice(
            $user,
            $command->amount
        );

        $this->invoiceRepository->save($invoice);
        $this->invoiceRepository->flush();
    }
}
