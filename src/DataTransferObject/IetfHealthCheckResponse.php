<?php

declare(strict_types=1);

namespace App\DataTransferObject;

use App\Enum\IetfHealthCheckStatus;

final readonly class IetfHealthCheckResponse implements \JsonSerializable
{
    /**
     * @param string[]                            $notes
     * @param array<string, IetfHealthCheckCheck> $checks
     * @param array<string, string>               $links
     */
    public function __construct(
        public IetfHealthCheckStatus $status,
        public ?string $version = null,
        public ?string $releaseId = null,
        public array $notes = [],
        public ?string $output = null,
        public array $checks = [],
        public array $links = [],
        public ?string $serviceId = null,
        public ?string $description = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $data = [
            'status' => $this->status,
        ];

        if ($this->version !== null) {
            $data['version'] = $this->version;
        }

        if ($this->releaseId !== null) {
            $data['releaseId'] = $this->releaseId;
        }

        if (\count($this->notes) > 0) {
            $data['notes'] = $this->notes;
        }

        if ($this->output !== null) {
            $data['output'] = $this->output;
        }

        if (\count($this->checks) > 0) {
            $data['checks'] = $this->checks;
        }

        if (\count($this->links) > 0) {
            $data['links'] = $this->links;
        }

        if ($this->serviceId !== null) {
            $data['serviceId'] = $this->serviceId;
        }

        if ($this->description !== null) {
            $data['description'] = $this->description;
        }

        return $data;
    }
}
