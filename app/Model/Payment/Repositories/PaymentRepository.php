<?php

declare(strict_types=1);

namespace App\Model\Payment\Repositories;

use App\Model\Infrastructure\Repositories\AbstractRepository;
use App\Model\Payment\Payment;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;

/**
 * Třída spravující platby.
 *
 * @author Jan Staněk <jan.stanek@skaut.cz>
 * @author Petr Parolek <petr.parolek@webnazakazku.cz>
 */
class PaymentRepository extends AbstractRepository
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, Payment::class);
    }

    /**
     * Vrací platbu podle id.
     */
    public function findById(?int $id): ?Payment
    {
        return $this->getRepository()->findOneBy(['id' => $id]);
    }

    /**
     * Vrací platbu podle id transakce.
     */
    public function findByTransactionId(string $transactionId): ?Payment
    {
        return $this->getRepository()->findOneBy(['transactionId' => $transactionId]);
    }

    /**
     * Uloží platbu.
     *
     * @throws ORMException
     */
    public function save(Payment $payment): void
    {
        $this->em->persist($payment);
        $this->em->flush();
    }

    /**
     * Odstraní platbu.
     *
     * @throws ORMException
     */
    public function remove(Payment $payment): void
    {
        foreach ($payment->getPairedApplications() as $pairedApplication) {
            $pairedApplication->setPayment(null);
            $this->em->persist($pairedApplication);
        }

        $this->em->remove($payment);
        $this->em->flush();
    }
}
