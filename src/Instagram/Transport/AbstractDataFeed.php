<?php

declare(strict_types=1);

namespace Instagram\Transport;

use GuzzleHttp\ClientInterface;
use Instagram\Auth\Session;
use Instagram\Exception\{InstagramAuthException, InstagramFetchException};
use Instagram\Utils\UserAgentHelper;

abstract class AbstractDataFeed
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @param ClientInterface $client
     * @param Session|null $session
     *
     * @throws InstagramAuthException
     */
    public function __construct(ClientInterface $client, ?Session $session)
    {
        if (!$session) {
            throw new InstagramAuthException('Please login before fetching data.');
        }

        $this->client  = $client;
        $this->session = $session;
    }

    /**
     * @param string $endpoint
     * @return \StdClass
     *
     * @throws InstagramFetchException
     */
    protected function fetchJsonDataFeed(string $endpoint): \StdClass
    {
        $headers = [
            'headers' => [
                'user-agent'       => UserAgentHelper::AGENT_DEFAULT,
                'x-requested-with' => 'XMLHttpRequest',
            ],
            'cookies' => $this->session->getCookies()
        ];

        $res = $this->client->request('GET', $endpoint, $headers);

        $data = (string)$res->getBody();
        $data = json_decode($data);

        if ($data === null) {
            throw new InstagramFetchException(json_last_error_msg());
        }

        return $data;
    }
}
