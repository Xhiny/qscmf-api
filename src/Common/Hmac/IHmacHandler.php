<?php

namespace QscmfApiCommon\Hmac;

interface IHmacHandler {

    public function verify(array $custom_keys = []): array;

    public function setSecretKeyResolver(callable $resolver): void;

    public function getCachePrefix(): string;

}
