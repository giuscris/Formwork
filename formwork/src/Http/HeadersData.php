<?php

namespace Formwork\Http;

class HeadersData extends RequestData
{
    /**
     * @param array<string, string> $data
     */
    public function __construct(array $data)
    {
        $this->initialize($data);
    }

    /**
     * @param array<string, string> $headers
     */
    protected function initialize(array $headers): void
    {
        $this->data = Header::fixHeaderNames($headers);
        ksort($this->data);
    }
}
