<?php

namespace Mindlahus\SymfonyAssets\Traits\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Mindlahus\SymfonyAssets\AbstractInterface\ResourceAbstract;
use Mindlahus\SymfonyAssets\Exception\ValidationFailedException;
use Mindlahus\SymfonyAssets\Helper\StringHelper;
use Symfony\Component\Validator\Validator\ValidatorInterface;

trait EntityTrait
{
    /**
     * @param ResourceAbstract $entityResource
     * @param string $method
     * @param $entity
     * @param ValidatorInterface $validator
     * @param EntityManagerInterface $em
     * @return mixed
     * @throws \Throwable
     */
    public static function EntityCreate(
        ResourceAbstract $entityResource,
        string $method,
        $entity,
        ValidatorInterface $validator,
        EntityManagerInterface $em
    )
    {
        return static::EntityChangeOrCreate(
            $entityResource,
            $method,
            $entity,
            $validator,
            $em,
            true
        );
    }

    /**
     * @param ResourceAbstract $entityResource
     * @param string $method
     * @param $entity
     * @param ValidatorInterface $validator
     * @param EntityManagerInterface $em
     * @return mixed
     * @throws \Throwable
     */
    public static function EntityChange(
        ResourceAbstract $entityResource,
        string $method,
        $entity,
        ValidatorInterface $validator,
        EntityManagerInterface $em
    )
    {
        return static::EntityChangeOrCreate(
            $entityResource,
            $method,
            $entity,
            $validator,
            $em
        );
    }

    /**
     * @param $entity
     * @param EntityManagerInterface $em
     */
    public static function EntityRemove(
        $entity,
        EntityManagerInterface $em
    ): void
    {
        $em->remove($entity);
        $em->flush();
    }

    /**
     * @param ResourceAbstract $entityResource
     * @param string $method
     * @param $entity
     * @param ValidatorInterface $validator
     * @param EntityManagerInterface $em
     * @param bool $persist
     * @return mixed
     * @throws \Throwable
     */
    private static function EntityChangeOrCreate(
        ResourceAbstract $entityResource,
        string $method,
        $entity,
        ValidatorInterface $validator,
        EntityManagerInterface $em,
        bool $persist = false
    )
    {
        $entityResource->{$method}($entity);
        $errors = $validator->validate($entity);
        if (\count($errors) > 0) {
            throw new ValidationFailedException($errors);
        }

        if ($persist === true) {
            $em->persist($entity);
        }
        $em->flush();

        return $entity;
    }

    /**
     * This helper method allows one to ignore the Time Zone reported by MySQL or the local Time Zone and,
     * just compare to dates considering the actual numbers (but of course, ignoring the real Time Zone)
     *
     * @param \DateTime|null $dateTime1
     * @param \DateTime|null $dateTime2
     * @param string|null $format
     * @param bool $strict
     * @return bool|\DateInterval|string
     */
    public static function validDateDiffInterval(
        \DateTime $dateTime1 = null,
        \DateTime $dateTime2 = null,
        string $format = null,
        bool $strict = true
    )
    {
        if (!$dateTime1 || !$dateTime2) {
            return false;
        }

        if ($strict === true) {
            $dateTime1 = new \DateTime($dateTime1->format('Ymd H:s:i'));
            $dateTime2 = new \DateTime($dateTime2->format('Ymd H:s:i'));
        }

        $interval = $dateTime1->diff($dateTime2);

        if ($format && $interval) {
            $interval = $interval->format($format);
        }

        // interval format can return 0 (int or string) and ?: will read this as false
        return $interval ?? false;
    }

    /**
     * \DateTime::ISO8601 is not compatible with the ISO8601 itself
     * For compatibility use \DateTime::ATOM or just c
     *
     * @param \DateTime $val
     * @param string $format
     * @return string
     */
    public static function getFormattedDateTime(\DateTime $val, string $format = \DateTime::ATOM): string
    {
        return StringHelper::dateFormat($val, $format);
    }
}