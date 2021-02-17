<?php

namespace tylercubell\Billing\Traits;

use tylercubell\Billing\Exceptions\BillingException;
use tylercubell\Billing\Events\BillingEvent;
use tylercubell\Billing\Models\PlanModel;
use tylercubell\Billing\Models\MetadataModel;

trait PlanTrait
{
    /**
     * Create plan.
     *
     * @see https://stripe.com/docs/api?lang=php#create_plan
     *
     * @param array $arguments
     * @return array|boolean
     */
    public function createPlan(array $arguments)
    {
        try {
            $plan = \Stripe\Plan::create($arguments);

            $result = $this->localUpdateOrCreatePlan($plan);
            event(new BillingEvent('billing.plan.create', $result));
            return $result;
        } catch(\Stripe\Error\Card $e) {
            throw new BillingException($e);
        } catch (\Stripe\Error\RateLimit $e) {
            throw new BillingException($e);
        } catch (\Stripe\Error\InvalidRequest $e) {
            throw new BillingException($e);
        } catch (\Stripe\Error\Authentication $e) {
            throw new BillingException($e);
        } catch (\Stripe\Error\ApiConnection $e) {
            throw new BillingException($e);
        } catch (\Stripe\Error\Base $e) {
            throw new BillingException($e);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Retrieve plan.
     *
     * @see https://stripe.com/docs/api?lang=php#retrieve_plan
     *
     * @param string $planId
     * @param bool $sync
     * @return array
     */
    public function retrievePlan(string $planId, bool $sync = false)
    {
        if ($sync) {
            try {
                $plan = \Stripe\Plan::retrieve($planId);
                return $this->localUpdateOrCreatePlan($plan);
            } catch(\Stripe\Error\Card $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\RateLimit $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\InvalidRequest $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\Authentication $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\ApiConnection $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\Base $e) {
                throw new BillingException($e);
            } catch (Exception $e) {
                throw $e;
            }
        }

        $plan = PlanModel::where('id', '=', $planId)->first();

        return ($plan !== null) ? $plan->toArray() : null;
    }

    /**
     * Update plan.
     *
     * @see https://stripe.com/docs/api?lang=php#update_plan
     *
     * @param string $planId
     * @param array $arguments
     * @return array
     */
    public function updatePlan(string $planId, array $arguments)
    {
        try {
            $plan = \Stripe\Plan::retrieve($planId);

            foreach ($arguments as $key => $value) {
                $plan->{$key} = $value;
            }

            $update = $plan->save();

            $result = $this->localUpdateOrCreatePlan($update);
            event(new BillingEvent('billing.plan.update', $result));
            return $result;
        } catch(\Stripe\Error\Card $e) {
            throw new BillingException($e);
        } catch (\Stripe\Error\RateLimit $e) {
            throw new BillingException($e);
        } catch (\Stripe\Error\InvalidRequest $e) {
            throw new BillingException($e);
        } catch (\Stripe\Error\Authentication $e) {
            throw new BillingException($e);
        } catch (\Stripe\Error\ApiConnection $e) {
            throw new BillingException($e);
        } catch (\Stripe\Error\Base $e) {
            throw new BillingException($e);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete plan.
     *
     * @see https://stripe.com/docs/api?lang=php#delete_plan
     *
     * @param string $planId
     * @return array
     */
    public function deletePlan(string $planId)
    {
        try {
            $plan = \Stripe\Plan::retrieve($planId);
            $delete = $plan->delete();

            $result = $this->localDeletePlan($delete);
            event(new BillingEvent('billing.plan.delete', $result));
            return $result;
        } catch(\Stripe\Error\Card $e) {
            throw new BillingException($e);
        } catch (\Stripe\Error\RateLimit $e) {
            throw new BillingException($e);
        } catch (\Stripe\Error\InvalidRequest $e) {
            throw new BillingException($e);
        } catch (\Stripe\Error\Authentication $e) {
            throw new BillingException($e);
        } catch (\Stripe\Error\ApiConnection $e) {
            throw new BillingException($e);
        } catch (\Stripe\Error\Base $e) {
            throw new BillingException($e);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * List all plans.
     *
     * @see https://stripe.com/docs/api?lang=php#list_plans
     *
     * @param array $arguments
     * @param bool $sync
     * @return array
     */
    public function listAllPlans(array $arguments = [], bool $sync = false)
    {
        if ($sync) {
            try {
                $hasMore = true;
                $arguments = array_merge($arguments, ['limit' => 100]);

                while ($hasMore) {
                    $plans = \Stripe\Plan::all($arguments);

                    foreach ($plans->data as $plan) {
                        $this->localUpdateOrCreatePlan($plan);
                    }

                    if ( ! $plans->has_more) {
                        $hasMore = false;
                    } else {
                        $lastPlan = end($plans->data);
                        $arguments['starting_after'] = $lastPlan->id;
                    }
                }
            } catch(\Stripe\Error\Card $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\RateLimit $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\InvalidRequest $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\Authentication $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\ApiConnection $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\Base $e) {
                throw new BillingException($e);
            } catch (Exception $e) {
                throw $e;
            }
        }

        $data = new PlanModel;

        if (isset($arguments['created'])) {
            if (isset($arguments['created']['gt'])) {
                $data = $data->where('created', '>', $arguments['created']['gt']);
            }

            if (isset($arguments['created']['gte'])) {
                $data = $data->where('created', '>=', $arguments['created']['gte']);
            }

            if (isset($arguments['created']['lt'])) {
                $data = $data->where('created', '<', $arguments['created']['lt']);
            }

            if (isset($arguments['created']['lte'])) {
                $data = $data->where('created', '<=', $arguments['created']['lte']);
            }
        }

        if (isset($arguments['limit'])) {
            $data = $data->limit($arguments['limit']);
        }

        $data = $data->orderBy('created', 'desc')->get()->toArray();

        return [
            'object'      => 'list',
            'data'        => $data,
            'has_more'    => false,
            'total_count' => count($data),
            'url'         => '/v1/plans'
        ];
    }

    /**
     * Update or create plan locally.
     *
     * @param $plan
     * @param string $changeId
     * @return array
     */
    public function localUpdateOrCreatePlan($plan, string $changeId = null)
    {
        $changeId = $changeId ? $changeId : $this->generateId();

        // Update or create plan
        $result = PlanModel::updateOrCreate(
            ['id' => $plan->id],
            [
                'amount'                 => $plan->amount,
                'created'                => $plan->created,
                'currency'               => $plan->currency,
                'interval'               => $plan->interval,
                'interval_count'         => $plan->interval_count,
                'livemode'               => $plan->livemode,
                'name'                   => $plan->name,
                'statement_descriptor'   => $plan->statement_descriptor,
                'trial_period_days'      => $plan->trial_period_days,
                'change_id'              => $changeId,
                'sync_id'                => $this->syncId()
            ]
        );

        // Update or create plan metadata
        if ( ! empty($plan->metadata)) {
            foreach ($plan->metadata as $key => $value) {
                MetadataModel::updateOrCreate(
                    ['stripe_id' => $plan->id],
                    [
                        'key'       => $key,
                        'value'     => $value,
                        'type'      => 'plan',
                        'change_id' => $changeId,
                        'sync_id'   => $this->syncId()
                    ]
                );
            }
        }

        // Delete plan metadata that doesn't exist in Stripe
        MetadataModel::where('type', '=', 'plan')
                     ->where('stripe_id', '=', $plan->id)
                     ->where('change_id', '<>', $changeId)
                     ->delete();

        return $result->toArray();
    }

    /**
     * Delete plan locally.
     *
     * @param $plan
     * @return array
     */
    public function localDeletePlan($plan)
    {
        $result = PlanModel::where('id', '=', $plan->id)->first();

        PlanModel::where('id', '=', $plan->id)->delete();

        MetadataModel::where('type', '=', 'plan')
                     ->where('stripe_id', '=', $plan->id)
                     ->delete();

        return $result;
    }
}
