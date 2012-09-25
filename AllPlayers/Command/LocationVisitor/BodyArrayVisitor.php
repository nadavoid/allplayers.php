<?php

namespace AllPlayers\Command\LocationVisitor;

use Guzzle\Http\EntityBody;
use Guzzle\Http\Message\RequestInterface;
use Guzzle\Service\Command\CommandInterface;
use Guzzle\Service\Command\LocationVisitor\AbstractVisitor;

/**
 * Visitor used to apply a body to a request
 */
class BodyArrayVisitor extends AbstractVisitor
{
    /**
     * {@inheritdoc}
     */
    public function visit(CommandInterface $command, RequestInterface $request, $key, $value)
    {
        $request->setBody(json_encode($value), 'application/json');
    }
}