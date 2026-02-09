<?php

namespace IIA\Framework\Controller;

use IIA\Framework\Database\Database;
use IIA\Framework\Controller\Response;

class Controller
{
    protected string $viewPath;
    protected string $template;
    private Database $database;

    private array $flashes;

    protected function render(string $view, array $variables = []): Response
    {
        ob_start(); // Start buffering the output
        extract($variables); // Convert array to variables
        require($this->viewPath . $view . '.html.php');
        $content = ob_get_clean(); // Store the output in a variable

        ob_start();
        $html = ob_get_clean();
        require($this->viewPath . $this->template . '.html.php');

        $response = new Response();
        $response->setStatusCode(200);
        $response->setContent($html);
        return $response;
    }

    public function getDatabase(): Database
    {
        return $this->database;
    }

    public function setDatabase(Database $database): void
    {
        $this->database = $database;
    }

    public function redirect(string $file, int $statusCode) {
        $response = new Response();
        $response->setStatusCode($statusCode);
        $response->setContent("");
        
        header("Location: ".$this->viewPath.$file."html.php", true, $statusCode);

        return $response;
    }

    protected function addFlash(string $type, string $message): void {
        $_SESSION['flashes'][$type][] = $message;
    }

    protected function getFlashes(): array {
        $flashes = $_SESSION['flashes'] ?? [];
        return $flashes;
    }
}