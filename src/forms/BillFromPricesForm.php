<?php
declare(strict_types=1);

namespace hipanel\modules\finance\forms;

use hipanel\modules\finance\models\Bill;
use hipanel\modules\finance\models\Charge;
use hipanel\modules\finance\models\Price;
use hipanel\modules\stock\models\Model;
use Yii;

class BillFromPricesForm extends Model
{
    private array $prices = [];

    private array $calculations = [];

    public function attributes(): array
    {
        return [
            'type',
            'time',
            'currency',
            'object_id',
            'client_id',
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'time' => Yii::t('hipanel', 'Time'),
            'type' => Yii::t('hipanel', 'Type'),
        ];
    }

    public function rules(): array
    {
        return [
            [['type', 'time'], 'string'],
            [['type', 'time'], 'required'],
        ];
    }

    public function createBillWithCharges(int $buyer_id, int $mainObjectId, string $mainObjectClass = 'device', array $prices = [], array $calculations = []): Bill
    {
        $this->prices = $prices;
        $this->calculations = $calculations;
        $bill = new Bill(['scenario' => 'create']);
        $bill->object_id = $mainObjectId;
        $bill->class = $mainObjectClass;
        $bill->client_id = $buyer_id;
        $bill->type = $this->type;
        $bill->time = $this->time;
        $bill->currency = reset($prices)->currency;
        $bill->unit = 'days';
        $bill->quantity = $this->numbersOfDays($bill) - $this->daysLeft($bill);
        $charges = $this->createCharges($bill);
        $bill->populateRelation('charges', $charges);
        $bill->sum = $this->getBillSum($charges);

        return $bill;
    }

    private function createCharges(Bill $bill): array
    {
        $classMap = [
            'monthly,hardware' => 'part',
        ];
        $charges = [];
        foreach ($this->prices as $price) {
            $charges[] = new Charge([
                'id' => 'fake_id', // this need for DynamicForm, if `id` is not exists it not displayed in Detalization.
                'class' => $classMap[$price->type],
                'object_id' => $price->object_id,
                'name' => $price->object->label,
                'ftype' => $this->type,
                'type' => $this->type,
                'sum' => $this->calculateSum($price, $bill),
                'quantity' => 1,
            ]);
        }

        return $charges;
    }

    private function getBillSum(array $charges = []): float
    {
        $sum = 0;
        foreach ($charges as $charge) {
            $sum += $charge->sum;
        }

        return -(float)number_format($sum, 2);
    }

    /**
     * Number of days in the current month
     * @param Bill $bill
     * @return int
     */
    private function numbersOfDays(Bill $bill): int
    {
        return (int)date('t', strtotime($bill->time));
    }

    /**
     * Days left until the end of the month
     * @param Bill $bill
     * @return int
     */
    private function daysLeft(Bill $bill): int
    {
        return (int)date('j', strtotime($bill->time));
    }

    private function calculateSum(Price $price, Bill $bill): string
    {
        if (isset($this->calculations[$price->object_id][$price->type])) {
            $sum = $this->calculations[$price->object_id][$price->type]['sum'];
        } else {
            $sum = $price->price;
        }

        return number_format(((float)$sum / $this->numbersOfDays($bill)) * $bill->quantity, 2);
    }
}
