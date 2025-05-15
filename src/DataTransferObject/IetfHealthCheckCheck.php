<?php

declare(strict_types=1);

namespace App\DataTransferObject;

use App\Enum\IetfHealthCheckStatus;

final readonly class IetfHealthCheckCheck implements \JsonSerializable
{
    /**
     * @param string[]              $affectedEndpoints
     * @param array<string, string> $links
     */
    public function __construct(
        public ?string $componentId = null,
        public ?string $componentType = null,
        public ?string $observedValue = null,
        public ?string $observedUnit = null,
        public ?IetfHealthCheckStatus $status = null,
        public array $affectedEndpoints = [],
        public ?\DateTimeInterface $time = null,
        public ?string $output = null,
        public array $links = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $data = [];

        if ($this->componentId !== null) {
            $data['componentId'] = $this->componentId;
        }

        if ($this->componentType !== null) {
            $data['componentType'] = $this->componentType;
        }

        if ($this->observedValue !== null) {
            $data['observedValue'] = $this->observedValue;
        }

        if ($this->observedUnit !== null) {
            $data['observedUnit'] = $this->observedUnit;
        }

        if ($this->status !== null) {
            $data['status'] = $this->status;
        }

        if (\count($this->affectedEndpoints) > 0) {
            $data['affectedEndpoints'] = $this->affectedEndpoints;
        }

        if ($this->time !== null) {
            $data['time'] = $this->time->format(\DateTimeInterface::RFC3339);
        }

        if ($this->output !== null) {
            $data['output'] = $this->output;
        }

        if (\count($this->links) > 0) {
            $data['links'] = $this->links;
        }

        return $data;
    }
}
