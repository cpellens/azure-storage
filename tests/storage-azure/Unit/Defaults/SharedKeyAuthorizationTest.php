<?php

namespace Azure\Storage\Tests\Defaults;

use Azure\Storage\Library\Defaults\Http\Request;
use Azure\Storage\Library\Defaults\Http\Uri;
use Azure\Storage\Library\Defaults\SharedKeyAuthentication;
use DateTime;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SharedKeyAuthentication::class)]
class SharedKeyAuthorizationTest extends TestCase
{
    #[Test]
    public function testAuthenticate(): void
    {
        $serverRequest           = new Request('PUT', new Uri('https://%s.blob.core.windows.net/%s'));
        $sharedKeyAuthentication = new SharedKeyAuthentication(
            'devstoreaccount1',
            'valid_api_key'
        );
        $sharedKeyAuthentication->authenticate($serverRequest);

        // Assert that the header was added
        $this->assertTrue($serverRequest->hasHeader(SharedKeyAuthentication::HEADER_NAME));

        $serverRequest = $serverRequest->withHeader('x-ms-date', (new DateTime())->format(DateTimeInterface::ATOM));
        $sharedKeyAuthentication->authenticate($serverRequest);
        $this->assertNotEquals(
            sprintf(
                SharedKeyAuthentication::SHARED_KEY_TEMPLATE,
                'devstoreaccount1:yn/B1exoxRjkk0mz+GjoIt5RVto7ZFywmON/NFkotnI='
            ),
            $serverRequest->getHeaderLine(SharedKeyAuthentication::HEADER_NAME)
        );
    }
}
