<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\Bundle\FixturesBundle\Suite;

use Sylius\Bundle\FixturesBundle\Fixture\FixtureRegistryInterface;
use Sylius\Bundle\FixturesBundle\Listener\ListenerRegistryInterface;
use Symfony\Component\Config\Definition\Processor;
use Webmozart\Assert\Assert;

final class SuiteFactory implements SuiteFactoryInterface
{
    private FixtureRegistryInterface $fixtureRegistry;

    private ListenerRegistryInterface $listenerRegistry;

    private Processor $optionsProcessor;

    public function __construct(
        FixtureRegistryInterface $fixtureRegistry,
        ListenerRegistryInterface $listenerRegistry,
        Processor $optionsProcessor
    ) {
        $this->fixtureRegistry = $fixtureRegistry;
        $this->listenerRegistry = $listenerRegistry;
        $this->optionsProcessor = $optionsProcessor;
    }

    public function createSuite(string $name, array $configuration): SuiteInterface
    {
        Assert::keyExists($configuration, 'fixtures');
        Assert::keyExists($configuration, 'listeners');

        $suite = new Suite($name);

        foreach ($configuration['fixtures'] as $fixtureAlias => $fixtureAttributes) {
            $this->addFixtureToSuite($suite, $fixtureAlias, $fixtureAttributes);
        }

        foreach ($configuration['listeners'] as $listenerName => $listenerAttributes) {
            $this->addListenerToSuite($suite, $listenerName, $listenerAttributes);
        }

        return $suite;
    }

    private function addFixtureToSuite(Suite $suite, string $fixtureAlias, array $fixtureAttributes): void
    {
        Assert::keyExists($fixtureAttributes, 'name');
        Assert::keyExists($fixtureAttributes, 'options');

        $fixture = $this->fixtureRegistry->getFixture($fixtureAttributes['name']);
        $fixtureOptions = $this->optionsProcessor->processConfiguration($fixture, $fixtureAttributes['options']);
        $fixturePriority = $fixtureAttributes['priority'] ?? 0;

        $suite->addFixture($fixture, $fixtureOptions, $fixturePriority);
    }

    private function addListenerToSuite(Suite $suite, string $listenerName, array $listenerAttributes): void
    {
        Assert::keyExists($listenerAttributes, 'options');

        $listener = $this->listenerRegistry->getListener($listenerName);
        $listenerOptions = $this->optionsProcessor->processConfiguration($listener, $listenerAttributes['options']);
        $listenerPriority = $listenerAttributes['priority'] ?? 0;

        $suite->addListener($listener, $listenerOptions, $listenerPriority);
    }
}
