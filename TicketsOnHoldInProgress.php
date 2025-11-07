<?php

namespace WHMCS\Module\Widget;

use WHMCS\Database\Capsule;
use WHMCS\Module\AbstractWidget;
use WHMCS\Carbon;

class TicketsOnHoldInProgress extends AbstractWidget
{
    protected $title = 'Tickets On Hold / In Progress';
    protected $description = 'Displays recent tickets with status On Hold or In Progress';
    protected $weight = 650;
    protected $cache = false;
    protected $requiredPermission = 'List Support Tickets';

    public function getData()
    {
        $tickets = Capsule::table('tbltickets')
            ->whereIn('status', ['On Hold', 'In Progress'])
            ->orderBy('lastreply', 'desc')
            ->limit(5)
            ->get(['id', 'tid', 'title', 'status', 'lastreply']);

        $countOnHold = Capsule::table('tbltickets')->where('status', 'On Hold')->count();
        $countInProgress = Capsule::table('tbltickets')->where('status', 'In Progress')->count();

        return [
            'tickets' => $tickets,
            'countOnHold' => $countOnHold,
            'countInProgress' => $countInProgress,
        ];
    }

    public function generateOutput($data)
    {
        $countOnHold = $data['countOnHold'];
        $countInProgress = $data['countInProgress'];

        $output = '<div class="icon-stats">
            <div class="row">
                <div class="col-sm-6">
                    <div class="item">
                        <div class="icon-holder text-center color-orange">
                            <i class="pe-7s-timer"></i>
                        </div>
                        <div class="data">
                            <div class="note"><a href="supporttickets.php?status=On+Hold">On Hold</a></div>
                            <div class="number"><span class="color-orange">' . $countOnHold . '</span> Tickets</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="item">
                        <div class="icon-holder text-center color-blue">
                            <i class="pe-7s-config"></i>
                        </div>
                        <div class="data">
                            <div class="note"><a href="supporttickets.php?status=In+Progress">In Progress</a></div>
                            <div class="number"><span class="color-blue">' . $countInProgress . '</span> Tickets</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>';

        $output .= '<div class="tickets-list" style="padding:5px 8px;">';

        if ($data['tickets']->isEmpty()) {
            $output .= '<div class="text-center text-muted p-3">No tickets found.</div>';
        } else {
            $i = 0;
            foreach ($data['tickets'] as $ticket) {
                $link = 'supporttickets.php?action=view&id=' . (int)$ticket->id;
                $statusColor = ($ticket->status == 'On Hold') ? 'color-orange' : 'color-blue';
                $timeAgo = Carbon::createFromFormat('Y-m-d H:i:s', $ticket->lastreply)->diffForHumans();
                $rowColor = ($i % 2 == 0) ? '#ffffff' : '#f8f8f8';
                $i++;

                $output .= '<div class="ticket" style="background:' . $rowColor . '; padding:5px 8px; border-radius:3px; margin-bottom:2px;">
                    <div class="pull-right ' . $statusColor . '">' . $timeAgo . '</div>
                    <a href="' . $link . '">#' . (int)$ticket->tid . ' - ' . htmlspecialchars($ticket->title) . '</a>
                </div>';
            }
        }

        $output .= '</div>';

        return $output;
    }
}
