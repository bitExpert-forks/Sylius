<?php

/*
 * This file is a part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Sylius\Bundle\OrderBundle\NumberGenerator;

use PhpSpec\ObjectBehavior;
use Sylius\Bundle\OrderBundle\NumberGenerator\SequentialOrderNumberGenerator;
use Sylius\Bundle\OrderBundle\NumberGenerator\SequentialOrderNumberGeneratorInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Order\Model\OrderSequenceInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

/**
 * @mixin SequentialOrderNumberGenerator
 *
 * @author Grzegorz Sadowski <grzegorz.sadowski@lakion.com>
 */
final class SequentialOrderNumberGeneratorSpec extends ObjectBehavior
{
    function let(EntityRepository $sequenceRepository, FactoryInterface $sequenceFactory)
    {
        $this->beConstructedWith($sequenceRepository, $sequenceFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Sylius\Bundle\OrderBundle\NumberGenerator\SequentialOrderNumberGenerator');
    }

    function it_implements_order_number_generator_interface()
    {
        $this->shouldImplement(SequentialOrderNumberGeneratorInterface::class);
    }

    function it_generates_order_number(EntityRepository $sequenceRepository, OrderSequenceInterface $sequence)
    {
        $sequence->getIndex()->willReturn(6);
        $sequenceRepository->findOneBy([])->willReturn($sequence);

        $sequence->incrementIndex()->shouldBeCalled();

        $this->generate()->shouldReturn('000000007');
    }

    function it_generates_order_number_when_sequence_is_null(
        EntityRepository $sequenceRepository,
        FactoryInterface $sequenceFactory,
        OrderSequenceInterface $sequence
    ) {
        $sequence->getIndex()->willReturn(0);

        $sequenceRepository->findOneBy([])->willReturn(null);

        $sequenceFactory->createNew()->willReturn($sequence);
        $sequenceRepository->add($sequence)->shouldBeCalled();

        $sequence->incrementIndex()->shouldBeCalled();

        $this->generate()->shouldReturn('000000001');
    }
}
