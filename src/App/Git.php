<?php


namespace App;


class Git
{
    private string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public static function init(string $token): self
    {
        return new self($token);
    }

    private function getSha(string $owner, string $repo, string $branch, string $filepath): string
    {
        $response = Request::init([
            CURLOPT_URL => "https://api.github.com/repos/$owner/$repo/contents/$filepath?ref=$branch",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'PHP-cURL',
            CURLOPT_HTTPHEADER => [
                "Authorization: token $this->token"
            ],
        ])->send();

        $data = json_decode($response['body'], true);
        if (isset($data['content']) || $data['status'] == 404) {
            return $data['sha'];
        } else {
            die("Ошибка получения содержимого: " . $response);
        }
    }

    public function push(string $owner, string $repo, string $branch, string $filepath, string $content, string $message = 'update')
    {
        return Request::init([
            CURLOPT_URL => "https://api.github.com/repos/$owner/$repo/contents/$filepath",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'PHP-cURL',
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => json_encode([
                "message" => $message,
                "content" => base64_encode($content),
                "sha" => $this->getSha($owner, $repo, $branch, $filepath),
                "branch" => $branch
            ]),
            CURLOPT_HTTPHEADER => [
                "Authorization: token $this->token",
                "Content-Type: application/json"
            ],
        ])->send();
    }
}