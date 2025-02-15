<?php

namespace Formwork\Panel\Controllers;

use Formwork\Http\Response;
use Formwork\Parsers\Json;
use Formwork\Statistics\Statistics;

final class StatisticsController extends AbstractController
{
    /**
     * Statistics@index action
     */
    public function index(Statistics $statistics): Response
    {
        if (!$this->hasPermission('statistics')) {
            return $this->forward(ErrorsController::class, 'forbidden');
        }

        $pageViews = $statistics->getPageViews();
        $sources = $statistics->getSources();
        $devices = $statistics->getDevices();

        return new Response($this->view('statistics.index', [
            'title'             => $this->translate('panel.statistics.statistics'),
            'statistics'        => Json::encode($statistics->getChartData(30)),
            'pageViews'         => array_slice($pageViews, 0, 15, preserve_keys: true),
            'totalViews'        => array_sum($pageViews),
            'sources'           => $sources,
            'totalSources'      => array_sum($sources),
            'devices'           => $devices,
            'totalDevices'      => array_sum($devices),
            'monthVisits'       => array_sum($statistics->getVisits(30)),
            'weekVisits'        => array_sum($statistics->getVisits(7)),
            'monthUniqueVisits' => array_sum($statistics->getUniqueVisits(30)),
            'weekUniqueVisits'  => array_sum($statistics->getUniqueVisits(7)),
        ]));
    }
}
