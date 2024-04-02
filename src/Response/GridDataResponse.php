<?php

declare(strict_types=1);

namespace Lemo\JqGrid\Response;

use Laminas\Diactoros\Response\JsonResponse;
use Lemo\JqGrid\Event\RendererEvent;
use Lemo\JqGrid\Grid;

use function count;

class GridDataResponse extends JsonResponse
{
    public function __construct(
        Grid $grid,
        int $status = 200,
        array $headers = [],
        int $encodingOptions = self::DEFAULT_JSON_FLAGS
    ) {
        $grid->checkIfIsPrepared();

        $grid->getAdapter()->prepare($grid);
        $grid->getAdapter()->fetchData();

        $data = $grid->getResultSet()->getData();
        $dataCount = count($data);

        $event = new RendererEvent();
        $event->setGrid($grid);

        $grid->getEventManager()->trigger(
            RendererEvent::EVENT_RENDER_DATA,
            $this,
            $event
        );

        $json = [
            'page' => $grid->getNumberOfCurrentPage(),
            'total' => $grid->getAdapter()->getNumberOfPages(),
            'records' => $grid->getAdapter()->getCountOfItemsTotal(),
            'rows' => [],
        ];

        for ($indexRow = 0; $indexRow < $dataCount; $indexRow++) {
            if (!empty($data[$indexRow]['rowId'])) {
                $rowId = $data[$indexRow]['rowId'];
            } else {
                $rowId = $indexRow + 1;
            }
            $json['rows'][] = [
                'id'   => $rowId,
                'cell' => $data[$indexRow]
            ];
        }

        $dataUser = $grid->getResultSet()->getDataUser();
        if (!empty($dataUser)) {
            $json['userdata'] = $dataUser;
        }

        parent::__construct($json, $status, $headers, $encodingOptions);
    }
}
