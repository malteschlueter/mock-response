<?php

declare(strict_types=1);

namespace App\Factory;

use App\DataTransferObject\IetfHealthCheckCheck;
use App\DataTransferObject\IetfHealthCheckResponse;
use App\Enum\IetfHealthCheckStatus;
use Assert\Assert;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
final class IetfHealthCheckResponseFactory
{
    private ?IetfHealthCheckStatus $status = null;
    private ?string $output = null;

    /**
     * @var array<string, int>
     */
    private array $checks = [];

    private function __construct()
    {
    }

    public static function new(): self
    {
        return new self();
    }

    public function create(): IetfHealthCheckResponse
    {
        $checks = [];

        foreach ($this->checks as $statusValue => $totalToCreate) {
            $statusCheck = IetfHealthCheckStatus::from($statusValue);

            for ($i = 0; $i < $totalToCreate; ++$i) {
                $checks[$statusCheck->value . ':check-' . $i] = new IetfHealthCheckCheck(
                    componentId: 'component-id-' . $i,
                    observedValue: (string) (random_int(0, 100) / 100),
                    observedUnit: 'percent',
                    status: $statusCheck,
                    output: 'check output ' . $i,
                );
            }

            if ($totalToCreate > 0) {
                $this->status = match (true) {
                    $this->status === null => $statusCheck,
                    $statusCheck === IetfHealthCheckStatus::Fail => $statusCheck,
                    $statusCheck === IetfHealthCheckStatus::Warn && $this->status !== IetfHealthCheckStatus::Fail => $statusCheck,
                    default => $this->status,
                };
            }
        }

        Assert::that($this->status)
            ->notNull('Status is required')
        ;

        return new IetfHealthCheckResponse(
            status: $this->status,
            output: $this->output,
            checks: $checks,
        );
    }

    public function withStatus(IetfHealthCheckStatus $status): self
    {
        $this->status = $status;

        return clone $this;
    }

    public function withOutput(string $output): self
    {
        $this->output = $output;

        return clone $this;
    }

    public function withChecks(IetfHealthCheckStatus $status, int $totalToCreate): self
    {
        $this->checks[$status->value] = $totalToCreate;

        return clone $this;
    }
}
