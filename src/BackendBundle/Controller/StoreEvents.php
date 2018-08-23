<?php


namespace BackendBundle\Controller;


class StoreEvents
{
    /**
     * El evento 'store.order' es lanzado cada vez que se crea una orden
     * en el sistema.
     *
     * El escucha del evento recibe una instancia de
     * Acme\StoreBundle\Event\FilterOrderEvent.
     *
     * @var string
     */
    const onStoreOrder = 'store.order';
}