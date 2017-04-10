<?php

namespace Mindlahus\SymfonyAssets\Traits;

use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandler;
use Mindlahus\SymfonyAssets\Exception\ValidationFailedException;
use Symfony\Component\HttpKernel\Exception\HttpException;

trait ControllerTrait
{
    /**
     * @param $data
     * @param ViewHandler $viewHandler
     * @param array $groups
     * @param array $options
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public static function Serialize($data, ViewHandler $viewHandler, array $groups = [], array $options = [])
    {
        $view = new View();
        $view->setData(['data' => $data]);
        if (!empty($groups)) {
            $view->getContext()->setGroups($groups);
        }

        $request = null;
        /**
         * Because of the anatomy of PUT requests, we use the new instance of the Request set inside ResourceAbstract
         */
        if ($options['entityResource'] ?? null) {
            $request = $options['entityResource']->getRequest();
        }

        return $viewHandler->handle($view, $request);
    }

    /**
     * todo : what is the shape of the response?
     * todo : what is the impact of extending FosController?
     * todo : how the response differs from findOneBy to findBy?
     *
     * $options = [
     *  arguments       required    array
     *  repository      required    string
     *  entityManager   required    \Doctrine\Common\Persistence\ObjectManager
     *  method          optional    string
     * ]
     *
     * @param array $options
     * @return mixed
     * @throws \Throwable
     */
    public static function findOneBy(array $options = [])
    {
        $data = $options['entityManager']->getRepository(
            $options['repository']
        )->{$options['method'] ?? 'findOneBy'}($options['arguments']);

        if (!$data) {
            throw new HttpException(404, "Entity not found");
        }

        return $data;
    }

    /**
     * $options = [
     *  arguments       required    array
     *  repository      required    string
     *  entityManager   required    \Doctrine\Common\Persistence\ObjectManager
     *  viewHandler     required    \FOS\RestBundle\View\ViewHandler
     *  method          optional    string
     *  groups          optional    array
     * ]
     *
     * @param array $options
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public static function SerializeFindOneBy(array $options)
    {
        return self::Serialize(
            self::findOneBy($options),
            $options['viewHandler'],
            $options['groups']
        );
    }

    /**
     * $options = [
     *  arguments       required    array
     *  repository      required    string
     *  entityManager   required    \Doctrine\Common\Persistence\ObjectManager
     *  method          optional    string
     * ]
     *
     * @param array $options
     * @return mixed
     */
    public static function findBy(array $options = [])
    {
        return $options['entityManager']->getRepository(
            $options['repository']
        )->{$options['method'] ?? 'findBy'}($options['arguments']);
    }

    /**
     * $options = [
     *  arguments       required    array
     *  repository      required    string
     *  entityManager   required    \Doctrine\Common\Persistence\ObjectManager
     *  viewHandler     required    \FOS\RestBundle\View\ViewHandler
     *  method          optional    string
     *  groups          optional    array
     * ]
     *
     * @param array $options
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public static function SerializeFindBy(array $options)
    {
        return self::Serialize(
            self::findBy($options),
            $options['viewHandler'],
            $options['groups']
        );
    }

    /**
     * $options = [
     *  repository      required    string
     *  entityManager   required    \Doctrine\Common\Persistence\ObjectManager
     *  method          optional    string
     * ]
     *
     * @param array $options
     * @return mixed
     */
    public static function findAll(array $options)
    {
        return $options['entityManager']->getRepository(
            $options['repository']
        )->{$options['method'] ?? 'findAll'}();
    }

    /**
     * $options = [
     *  repository      required    string
     *  entityManager   required    \Doctrine\Common\Persistence\ObjectManager
     *  viewHandler     required    \FOS\RestBundle\View\ViewHandler
     *  method          optional    string
     *  groups          optional    array
     * ]
     *
     * @param array $options
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public static function SerializeFindAll(array $options)
    {
        return self::Serialize(
            self::findAll($options),
            $options['viewHandler'],
            $options['groups']
        );
    }

    /**
     * $options = [
     *  entityResource  required    Class that extends \Mindlahus\SymfonyAssets\AbstractInterface\ResourceAbstract
     *  method          required    string
     *  entity          required    Instance of an Entity class
     *  persist         optional    boolean
     *  entityManager   required    \Doctrine\Common\Persistence\ObjectManager
     *  validator       required    ValidatorInterface
     * ]
     *
     * @param array $options
     * @return mixed
     */
    public static function persistenceHandler(array $options)
    {
        $options['entityResource']->{$options['method']}($options['entity']);
        $errors = $options['validator']->validate($options['entity']);
        if (count($errors) > 0) {
            throw new ValidationFailedException($errors);
        }

        if ($options['persist'] ?? null) {
            $options['entityManager']->persist($options['entity']);
        }

        $options['entityManager']->flush();

        return $options['entity'];

    }

    /**
     * $options = [
     *  entityResource  required    Class that extends \Mindlahus\SymfonyAssets\AbstractInterface\ResourceAbstract
     *  method          required    string
     *  entity          required    Instance of an Entity class
     *  persist         optional    boolean
     *  entityManager   required    \Doctrine\Common\Persistence\ObjectManager
     *  viewHandler     required    \FOS\RestBundle\View\ViewHandler
     *  groups          optional    array
     *  validator       required    ValidatorInterface
     * ]
     *
     * @param array $options
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public static function SerializedPersistenceHandler(array $options)
    {
        return self::Serialize(
            self::persistenceHandler($options),
            $options['viewHandler'],
            $options['groups'],
            $options
        );
    }
}