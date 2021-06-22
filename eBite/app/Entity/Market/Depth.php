<?php


namespace App\Entity\Market;


use App\Entity\BaseEntity;
use Illuminate\Support\Collection;

class Depth extends BaseEntity
{

    /**
     * @var Collection
     */
    public $in;

    /**
     * @var Collection
     */
    public $out;

    /**
     * @var string
     */
    public $symbol;

    /**
     * @var int
     */
    public $currency_match_id;

    /**
     * Depth constructor.
     *
     * @param string          $symbol
     * @param null|Collection $buys
     * @param null|Collection $sells
     * @param int             $currency_match_id
     */
    public function __construct($symbol, $buys = null, $sells = null, $currency_match_id = 0)
    {
        $this->in = collect();
        $this->out = collect();

        $this->symbol = $symbol;
        $this->currency_match_id = $currency_match_id;
        $buys && $buys->each(function ($item) {
            $this->pushTxBuy($item);
        });
        $sells && $sells->each(function ($item) {
            $this->pushTxSell($item);
        });
    }

    /**
     * @param DepthTx $buy
     */
    public function pushTxBuy($buy)
    {
        $this->in->push($buy);
        $buy->sum = $this->in->sum('amount');

        $buy->index = __('api.买:index', [
            'index' => $this->in->count()
        ]);
    }

    /**
     * @param DepthTx $sell
     */
    public function pushTxSell($sell)
    {
        $this->out->push($sell);
        $sell->sum = $this->out->sum('amount');

        $sell->index = __('api.卖:index', [
            'index' => $this->out->count()
        ]);

    }
}
