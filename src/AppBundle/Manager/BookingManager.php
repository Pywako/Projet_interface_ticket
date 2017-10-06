<?php

namespace AppBundle\Manager;

use AppBundle\Entity\Booking;
use AppBundle\Entity\Ticket;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


class BookingManager
{
    private $request;
    private $session;
    private $em;

    public function __construct(RequestStack $requestStack, SessionInterface $session, EntityManagerInterface $entityManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->session = $session;
        $this->em = $entityManager;
    }

    public function createBooking()
    {
        $booking = new Booking();
        return $booking;
    }

    public function getBookingInSession()
    {
        $booking = $this->session->get('booking');
        return $booking;
    }

    public function setBookingInSession(Booking $booking)
    {
        $this->request->getSession()->set('booking', $booking);
    }

    public function prepareTicketForm(Booking $booking)
    {
        $nbTicket = $booking->getNbTicket();

        /**
         * @var $ticketsCollection ArrayCollection
         */
        $ticketsCollection = $booking->getTickets();
        while ($nbTicket != $ticketsCollection->count()) {
            if ($nbTicket > $ticketsCollection->count()) {
                $booking->addTicket(new Ticket());
            } else {
                $ticketsCollection->remove($ticketsCollection->last());
            }
        }
    }

    public function prepareCart(Booking $booking)
    {
        $tickets = $booking->getTickets();
        foreach ($tickets as $key => $ticket) {
            $price = $this->computePrice($booking->getType(), $ticket->getDateNaissance(), $ticket->getReduit());
            $ticket->setPrix($price);
        }
    }

    /**
     * @param int $type
     * @param /Datetime $dateNaissance
     * @param bool $reduit
     * @return float|null
     */
    private function computePrice($type, $dateNaissance, $reduit)
    {
        $date = new \DateTime();

        // Calcul de l'age du client
        $age = $date->diff($dateNaissance)->y;
        $price = null;

        if ($age < 4) {
            $price = Ticket::TARIF_BABY;
        } elseif ($age < 12) {
            $price = Ticket::TARIF_CHILD;
        } elseif ($age < 60) {
            $price = Ticket::TARIF_STANDARD;
        } elseif ($age >= 60) {
            $price = Ticket::TARIF_SENIOR;
        }

        // tarif réduit

        if ($age >= 12 && $reduit === true) {
            $price = Ticket::TARIF_HALF;
        }

        if ($type == Booking::TYPE_HALF_DAY && $age >= 4) {
            $price = $price * Ticket::COEFICIENT_HALF_DAY;
        }

        return $price;
    }

    public function registerBookingInBdd(Booking $booking)
    {
        $booking->setDateResa(new \DateTime());

        //Génération code de réservation
        $booking->setCode(md5(uniqid(rand(), true)));

        $this->em->persist($booking);
        $this->em->flush();
    }

    public function emptySession()
    {
        $this->session->invalidate();
    }

    public function prepareBookingForDisplay(Booking $booking)
    {
        $tickets = $booking->getTickets();
        foreach ($tickets as $ticket) {
            if ($ticket->getReduit() == true) {
                $ticket->setReduit("oui");
            } elseif ($ticket->getReduit() == false) {
                $ticket->setReduit("non");
            } else {
                return false;
            };
        }
        if($booking->getType() == 1)
        {
            $booking->setType('billet journée');
        }
        elseif ($booking->getType() == 2)
        {
            $booking->setType('billet demi-journée');
        }
        return $booking;
    }
}
