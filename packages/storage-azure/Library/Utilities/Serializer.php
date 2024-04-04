<?php

namespace Azure\Storage\Library\Utilities;

use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerAwareTrait;

/**
 * @template T
 */
readonly class Serializer implements \Amp\Serialization\Serializer
{
    use SerializerAwareTrait;

    /**
     * @param class-string<T> $className
     */
    public function __construct(
        private EncoderInterface $encoder,
        private string $className
    ) {
        $this->serializer = new \Symfony\Component\Serializer\Serializer(
            [
                new ObjectNormalizer()
            ],
            [
                $this->encoder
            ]
        );
    }

    /**
     * @param T $data
     */
    public function serialize($data): string
    {
        return $this->encoder->encode($data, $this->encoder::class);
    }

    /**
     * @return T
     */
    public function unserialize(string $data)
    {
        return $this->serializer->decode($data, $this->encoder::class);
    }
}
