<?php

namespace App\Tests\Unit\Core\Invoice\UserInterface\Cli;

use App\Core\Invoice\Application\DTO\InvoiceDTO;
use App\Core\Invoice\UserInterface\Cli\GetInvoices;
use App\Common\Bus\QueryBusInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class GetInvoicesTest extends TestCase
{
    private QueryBusInterface|\PHPUnit\Framework\MockObject\MockObject $queryBus;

    private GetInvoices $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->queryBus = $this->createMock(QueryBusInterface::class);
        $this->command = new GetInvoices($this->queryBus);
    }

    public function test_execute_with_results(): void
    {
        $invoices = [
            new InvoiceDTO(1, 'user1@example.com', 10000),
            new InvoiceDTO(2, 'user2@example.com', 20000),
        ];

        $this->queryBus->expects(self::once())
            ->method('dispatch')
            ->willReturn($invoices);

        $input = new ArrayInput(['status' => 'new', 'amount' => 5000]);
        $output = new BufferedOutput();

        $this->command->run($input, $output);

        $result = $output->fetch();
        $this->assertStringContainsString('1', $result);
        $this->assertStringContainsString('2', $result);
    }

    public function test_execute_no_results(): void
    {
        $this->queryBus->expects(self::once())
            ->method('dispatch')
            ->willReturn([]);

        $input = new ArrayInput(['status' => 'new', 'amount' => 5000]);
        $output = new BufferedOutput();

        $this->command->run($input, $output);

        $this->assertStringContainsString('Brak faktur spełniających podane kryteria.', $output->fetch());
    }
}
