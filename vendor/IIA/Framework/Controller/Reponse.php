<?php

namespace IIA\Framework\Response;

class Response {
    private int $code;
    private string $content;


    public function setStatusCode(int $code) {
        $this->code = $code;
    }

    public function setContent(string $content): void {
        $this->content = $content;
    }
}