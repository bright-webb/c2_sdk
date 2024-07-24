<?php
namespace C2\SDK;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class C2Client {
  private $apiKey;

  private $client;
  private $dir = null;
  private $baseUrl = 'https://c2.bevynile.com';

  public function __construct(){
    $this->client = new Client([
      'base_uri' => $this->baseUrl
  ]);
  
  }

  public function configUrl(string $url){
    $this->baseUrl = $url;
  }

  public function getBaseUrl(){
    return $this->baseUrl;
  }
  public function setApiKey(string $apiKey){
    $this->apiKey = $apiKey;
  }

  public function getApiKey(): string {
    return $this->apiKey;
  }

  public function setDirectory(string $path) {
    $this->dir = $path;
    }


public function transport($filePath, $fileName){

try {
    $response = $this->client->request('POST', '/api/upload', [
        'multipart' => [
            [
                'name'     => 'file',
                'contents' => fopen($filePath, 'r'),
                'filename' => $fileName
            ],
            [
                'name'     => 'dir',
                'contents' => $this->dir
            ],

        ],
        'headers' => [
            'API_KEY' => $this->apiKey
        ]
    ]);

    return $response->getBody()->getContents();


} catch (RequestException $e) {
    error_log("File transport error: " . $e->getMessage());
        if ($e->hasResponse()) {
            $body = $e->getResponse()->getBody();
            error_log("Response body: " . $body);
            return [$e->getResponse()->getStatusCode(), $body];
        } else {
            return [500, $e->getMessage()];
        }
}
}

private function request($method, $endpoint, $data = [], $headers = []) {
        $url = $this->baseUrl . $endpoint;
        $client = new Client();
        $options = [
            'headers' => array_merge([
                'API_KEY' => $this->apiKey,
                'Accept' => 'application/json'
            ], $headers)
        ];

        if ($method == 'GET') {
            $options['query'] = $data;
        } else {
            $options['form_params'] = $data;
        }

        try {
            $response = $client->request($method, $url, $options);
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

public function download(string $file, string $directory = null){
    $data = ['file' => $file];
        if ($directory) {
            $data['directory'] = $directory;
        }
        return $this->request('GET', '/download', $data);
}

public function delete(string $file, string $directory = null){
    $data = ['file' => $file];
        if ($directory) {
            $data['directory'] = $directory;
        }
        return $this->request('DELETE', '/delete', $data);
}

public function getMetaData(string $file){
    return $this->request('GET', '/metadata', ['file' => $file]);
}

public function getUrl(string $file){
    return $this->request('GET', '/url', ['file' => $file]);
}

public function getSize(string $file){
    return $this->request('GET', '/size', ['file' => $file]);
}

public function fileExists(string $file){
    return $this->request('GET', 'file-exists', ['file' => $file]);
}

public function generatePressignedUrl(string $file, string $expiryTime){
    return $this->request('GET', '/presigned-url', ['file' => $file, 'expiryTime' => $expiryTime]);
}

}