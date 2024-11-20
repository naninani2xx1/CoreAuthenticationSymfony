<?php

namespace App\Traits;
use Doctrine\ORM\Mapping as ORM;
trait TimeStampsTrait
{
    #[ORM\Column(type: 'datetime', nullable:  true)]
    private ?\DateTime $createAt;

    #[ORM\Column(type: 'datetime', nullable:  true)]
    private ?\DateTime $updatedAt;

    /**
     * @return \DateTime|null
     */
    public function getCreateAt(): ?\DateTime
    {
        return $this->createAt;
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime|null $createAt
     */
    public function setCreateAt(?\DateTime $createAt): void
    {
        $this->createAt = $createAt;
    }

    /**
     * @param \DateTime|null $updatedAt
     */
    public function setUpdatedAt(?\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}