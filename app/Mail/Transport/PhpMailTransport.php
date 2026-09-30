<?php

namespace App\Mail\Transport;

use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;

/**
 * Sends mail with PHP's built-in mail(). Some shared hosts (like the free one this site runs on) switch off
 * proc_open, which Laravel's "sendmail" driver needs, but still allow mail().
 */
class PhpMailTransport extends AbstractTransport
{
    protected function doSend(SentMessage $message): void
    {
        $recipients = array_map(fn ($address) => $address->getAddress(), $message->getEnvelope()->getRecipients());

        [$subject, $headers, $body] = self::prepare($message->toString());

        if (! @mail(implode(', ', $recipients), $subject, $body, $headers)) {
            throw new TransportException('PHP mail() did not accept the message for delivery.');
        }
    }

    /**
     * Splits a finished message into what mail() wants: the subject, the other headers (To and Subject are
     * added by mail() itself), and the body - all with plain line feeds, because Unix mail programs can turn
     * CRLF into doubled carriage returns.
     *
     * @return array{0: string, 1: string, 2: string} subject, headers, body
     */
    public static function prepare(string $rawMessage): array
    {
        [$rawHeaders, $body] = array_pad(preg_split("/\r?\n\r?\n/", $rawMessage, 2), 2, '');

        $lines = [];
        foreach (preg_split("/\r?\n/", $rawHeaders) as $line) {
            if ($line !== '' && ($line[0] === ' ' || $line[0] === "\t") && $lines) {
                $lines[array_key_last($lines)] .= "\n".$line;   // a folded header continues the previous one
                continue;
            }
            $lines[] = $line;
        }

        $subject = '';
        $kept = [];
        foreach ($lines as $header) {
            $name = strtolower(strstr($header, ':', true) ?: '');

            if ($name === 'subject') {
                $subject = trim(substr($header, strpos($header, ':') + 1));
            } elseif ($name !== 'to' && $header !== '') {
                $kept[] = $header;
            }
        }

        return [$subject, implode("\n", $kept), str_replace("\r\n", "\n", $body)];
    }

    public function __toString(): string
    {
        return 'phpmail://default';
    }
}
