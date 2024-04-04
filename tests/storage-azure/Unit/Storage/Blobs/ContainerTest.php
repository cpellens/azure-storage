<?php

namespace Azure\Storage\Unit\Storage\Blobs;

use Azure\Storage\Api\AdapterInterface;
use Azure\Storage\Api\AuthenticationInterface;
use Azure\Storage\Api\Storage\ContainerInterface;
use Azure\Storage\Library\Adapter;
use Azure\Storage\Library\Defaults\Environment;
use Azure\Storage\Library\Storage\Blobs\Container;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

#[CoversClass(ContainerInterface::class)]
#[CoversClass(Container::class)]
class ContainerTest extends TestCase
{
    private ClientInterface  $http;
    private AdapterInterface $adapter;

    public function testGetMetadata(): void
    {
        $testContainer = $this->adapter->getContainer('test-container');
        $metadata      = $testContainer->getMetadata();

        $this->assertTrue($metadata->offsetExists('name'));
        $this->assertSame('test-container', $metadata->offsetGet('name'));
        $this->assertTrue(isset($metadata['another']));
        $this->assertSame('key, to, test', $metadata['another']);
        $this->assertEmpty($this->queue);

        $this->assertSame($metadata, $testContainer->getMetadata());
    }

    /**
     * @throws Exception
     */
    public function testListBlobs(): void
    {
        $xml = <<<RESPONSE_XML
<?xml version="1.0" encoding="utf-8"?>  
<EnumerationResults ServiceEndpoint="https://myaccount.blob.core.windows.net/"  ContainerName="mycontainer">  
  <Prefix>string-value</Prefix>  
  <Marker>string-value</Marker>  
  <MaxResults>int-value</MaxResults>  
  <Delimiter>string-value</Delimiter>  
  <Blobs>  
    <Blob>  
      <Name>blob-name</name>  
      <Snapshot>date-time-value</Snapshot>  
      <VersionId>date-time-vlue</VersionId>
      <IsCurrentVersion>true</IsCurrentVersion>
      <Deleted>true</Deleted>
      <Properties> 
        <Creation-Time>date-time-value</Creation-Time>
        <Last-Modified>date-time-value</Last-Modified>  
        <Etag>etag</Etag>
        <Owner>owner user id</Owner>
        <Group>owning group id</Group>
        <Permissions>permission string</Permissions>
        <Acl>access control list</Acl>
        <ResourceType>file | directory</ResourceType>
        <Placeholder>true</Placeholder>
        <Content-Length>size-in-bytes</Content-Length>  
        <Content-Type>blob-content-type</Content-Type>  
        <Content-Encoding />  
        <Content-Language />  
        <Content-MD5 />  
        <Cache-Control />  
        <x-ms-blob-sequence-number>sequence-number</x-ms-blob-sequence-number>  
        <BlobType>BlockBlob|PageBlob|AppendBlob</BlobType>  
        <AccessTier>tier</AccessTier>  
        <LeaseStatus>locked|unlocked</LeaseStatus>  
        <LeaseState>available | leased | expired | breaking | broken</LeaseState>  
        <LeaseDuration>infinite | fixed</LeaseDuration>  
        <CopyId>id</CopyId>  
        <CopyStatus>pending | success | aborted | failed </CopyStatus>  
        <CopySource>source url</CopySource>  
        <CopyProgress>bytes copied/bytes total</CopyProgress>  
        <CopyCompletionTime>datetime</CopyCompletionTime>  
        <CopyStatusDescription>error string</CopyStatusDescription>  
        <ServerEncrypted>true</ServerEncrypted> 
        <CustomerProvidedKeySha256>encryption-key-sha256</CustomerProvidedKeySha256>
        <EncryptionContext>encryption-context<EncryptionContext>
        <EncryptionScope>encryption-scope-name</EncryptionScope>
        <IncrementalCopy>true</IncrementalCopy>
        <AccessTierInferred>true</AccessTierInferred>
        <AccessTierChangeTime>datetime</AccessTierChangeTime>
        <DeletedTime>datetime</DeletedTime>
        <RemainingRetentionDays>no-of-days</RemainingRetentionDays>
        <TagCount>number of tags between 1 to 10</TagCount>
        <RehydratePriority>rehydrate priority</RehydratePriority>
        <Expiry-Time>date-time-value</Expiry-Time>
      </Properties>  
      <Metadata>     
        <Name>value</Name>  
      </Metadata>  
      <Tags>
          <TagSet>
              <Tag>
                  <Key>TagName</Key>
                  <Value>TagValue</Value>
              </Tag>
          </TagSet>
      </Tags>
      <OrMetadata />
    </Blob>  
    <BlobPrefix>  
      <Name>blob-prefix</Name>  
    </BlobPrefix>  
  </Blobs>  
  <NextMarker />  
</EnumerationResults>
RESPONSE_XML;

        $body = $this->createConfiguredStub(StreamInterface::class, [
            'getContents' => $xml
        ]);

        $responseObject = $this->createConfiguredStub(ResponseInterface::class, [
            'getBody'       => $body,
            'getStatusCode' => 200
        ]);

        $this->http->method('sendRequest')->willReturn($responseObject);

        $container = $this->adapter->getContainer('test-container');
        $blobs     = iterator_to_array($container->list());
    }

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->http  = $this->createStub(ClientInterface::class);
        $environment = new Environment('', $this->createStub(AuthenticationInterface::class));
        $environment->setHttpClient($this->http);
        $this->adapter = new Adapter($environment);
    }
}
