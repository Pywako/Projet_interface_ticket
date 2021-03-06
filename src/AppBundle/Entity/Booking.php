<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Validator\Constraints as BookingAssert;

/**
 * Booking
 *
 * @ORM\Table(name="booking")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\BookingRepository")
 * @BookingAssert\ConstraintHalfDayBooking(groups={"step1"}, message="halfdayBooking")
 * @BookingAssert\ConstraintFull(groups={"step1"}, message="full")
 */
class Booking
{
    const TYPE_DAY      = 1;
    const TYPE_HALF_DAY = 2;
    const FULL          = 5;
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @Assert\Email(
     *     message = "email.not.correct",
     *     checkMX=true,
     *     groups={"step1"})
     * @Assert\NotNull(groups={"step1"}, message="email.not.null")
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

       /**
     * @var \DateTime
     * @Assert\DateTime(groups={"step1"}, message="dateVisit.not.correct")
     * @Assert\Range(
     *     min = "today",
     *     max = "next year UTC",
     *     groups={"step1"},
     *     invalidMessage="dateVisit.range.not.valid",
     *     minMessage="dateVisit.range.min",
     *     maxMessage="dateVisit.range.max")
     * @BookingAssert\ConstraintNotTuesdaySunday(groups={"step1"}, message="dateVisit.not.tuesdaySunday")
     * @BookingAssert\ConstraintHoliday(groups={"step1"}, message="dateVisit.not.holyday")
     * @Assert\NotNull(groups={"step1"}, message="dateVisit.not.null")
     * @ORM\Column(name="dateVisit", type="date")
     */
    private $dateVisit;

    /**
     * @var \DateTime
     * @Assert\DateTime(groups={"step2"})
     * @ORM\Column(name="dateResa", type="datetime")
     */
    private $dateResa;

    /**
     * @var int
     * @Assert\Range(
     *     min = 1,
     *     max = 10,
     *     groups={"step1"},
     *     invalidMessage="nbTicket.not.correct",
     *     minMessage="nbTicket.min",
     *     maxMessage="nbTicket.max")
     * @Assert\NotNull(groups={"step1"}, message="nbTicket.not.null")
     * @ORM\Column(name="nbTicket", type="smallint")
     */
    private $nbTicket;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255)
     */
    private $code;

    /**
     * @var int
     * @Assert\NotNull(groups={"step1"}, message="type.not.null")
     * @ORM\Column(name="type", type="smallint", length=255)
     */
    private $type;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Ticket", mappedBy="booking", cascade="persist")
     *
     * @Assert\Valid()
     */
    private $tickets;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Booking
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set dateVisit
     *
     * @param \DateTime $dateVisit
     *
     * @return Booking
     */
    public function setDateVisit($dateVisit)
    {
        $this->dateVisit = $dateVisit;

        return $this;
    }

    /**
     * Get dateVisit
     *
     * @return \DateTime
     */
    public function getDateVisit()
    {
        return $this->dateVisit;
    }

    /**
     * Set dateResa
     *
     * @param \DateTime $dateResa
     *
     * @return Booking
     */
    public function setDateResa($dateResa)
    {
        $this->dateResa = $dateResa;

        return $this;
    }

    /**
     * Get dateResa
     *
     * @return \DateTime
     */
    public function getDateResa()
    {
        return $this->dateResa;
    }

    /**
     * Set nbTicket
     *
     * @param integer $nbTicket
     *
     * @return Booking
     */
    public function setNbTicket($nbTicket)
    {
        $this->nbTicket = $nbTicket;

        return $this;
    }

    /**
     * Get nbTicket
     *
     * @return int
     */
    public function getNbTicket()
    {
        return $this->nbTicket;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return Booking
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Booking
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tickets = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add ticket
     *
     * @param \AppBundle\Entity\Ticket $ticket
     *
     * @return Booking
     */
    public function addTicket(\AppBundle\Entity\Ticket $ticket)
    {
        $this->tickets[] = $ticket;
        $ticket->setBooking($this);
        return $this;
    }

    /**
     * Remove ticket
     *
     * @param \AppBundle\Entity\Ticket $ticket
     */
    public function removeTicket(\AppBundle\Entity\Ticket $ticket)
    {
        $this->tickets->removeElement($ticket);
    }

    /**
     * Get tickets
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTickets()
    {
        return $this->tickets;
    }

    public function getTotalPrice()
    {
        $price = 0;
        foreach ($this->getTickets() as $ticket)
        {
            $price += $ticket->getPrix();
        }
        return $price;
    }
}
