<?php

namespace tylercubell\Billing\Traits;

use tylercubell\Billing\Exceptions\BillingException;
use tylercubell\Billing\Events\BillingEvent;
use tylercubell\Billing\Models\CouponModel;
use tylercubell\Billing\Models\MetadataModel;

trait CouponTrait
{
    /**
     * Create coupon.
     *
     * @see https://stripe.com/docs/api?lang=php#create_coupon
     *
     * @param array $arguments
     * @return array|boolean
     */
    public function createCoupon(array $arguments)
    {
        try {
            $coupon = \Stripe\Coupon::create($arguments);

            $result = $this->localUpdateOrCreateCoupon($coupon);
            event(new BillingEvent('billing.coupon.create', $result));
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
     * Retrieve coupon.
     *
     * @see https://stripe.com/docs/api?lang=php#retrieve_coupon
     *
     * @param string $couponId
     * @param bool $sync
     * @return array
     */
    public function retrieveCoupon(string $couponId, bool $sync = false)
    {
        if ($sync) {
            try {
                $coupon = \Stripe\Coupon::retrieve($couponId);
                return $this->localUpdateOrCreateCoupon($coupon);
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

        $coupon = CouponModel::where('id', '=', $couponId)->first();

        return ($coupon !== null) ? $coupon->toArray() : null;
    }

    /**
     * Update coupon.
     *
     * @see https://stripe.com/docs/api?lang=php#update_coupon
     *
     * @param string $couponId
     * @param array $arguments
     * @return array|boolean
     */
    public function updateCoupon(string $couponId, array $arguments)
    {
        try {
            $coupon = \Stripe\Coupon::retrieve($couponId);

            foreach ($arguments as $key => $value) {
                $coupon->{$key} = $value;
            }

            $update = $coupon->save();
            
            $result = $this->localUpdateOrCreateCoupon($update);
            event(new BillingEvent('billing.coupon.update', $result));
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
     * Delete coupon.
     *
     * @see https://stripe.com/docs/api?lang=php#delete_coupon
     *
     * @param string $couponId
     * @return array
     */
    public function deleteCoupon(string $couponId)
    {
        try {
            $coupon = \Stripe\Coupon::retrieve($couponId);
            $delete = $coupon->delete();

            $result = $this->localDeleteCoupon($delete);
            event(new BillingEvent('billing.coupon.delete', $result));
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
     * List all coupons.
     *
     * @see https://stripe.com/docs/api?lang=php#list_coupons
     *
     * @param array $arguments
     * @param bool $sync
     * @return array
     */
    public function listAllCoupons(array $arguments = [], bool $sync = false)
    {
        if ($sync) {
            try {
                $hasMore = true;
                $arguments = array_merge($arguments, ['limit' => 100]);

                while ($hasMore) {
                    $coupons = \Stripe\Coupon::all($arguments);

                    foreach ($coupons->data as $coupon) {
                        $this->localUpdateOrCreateCoupon($coupon);
                    }

                    if ( ! $coupons->has_more) {
                        $hasMore = false;
                    } else {
                        $lastCoupon = end($coupons->data);
                        $arguments['starting_after'] = $lastCoupon->id;
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

        $data = new CouponModel;

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
            'url'         => '/v1/coupons'
        ];
    }

    /**
     * Update or create coupon locally.
     *
     * @param $coupon
     * @param string $changeId
     * @return array
     */
    public function localUpdateOrCreateCoupon($coupon, string $changeId = null)
    {
        $changeId = $changeId ? $changeId : $this->generateId();

        // Update or create coupon
        $result = CouponModel::updateOrCreate(
            ['id' => $coupon->id],
            [
                'amount_off'           => $coupon->amount_off,
                'created'              => $coupon->created,
                'currency'             => $coupon->currency,
                'duration'             => $coupon->duration,
                'duration_in_months'   => $coupon->duration_in_months,
                'livemode'             => $coupon->livemode,
                'max_redemptions'      => $coupon->max_redemptions,
                'percent_off'          => $coupon->percent_off,
                'redeem_by'            => $coupon->redeem_by,
                'times_redeemed'       => $coupon->times_redeemed,
                'valid'                => $coupon->valid,
                'change_id'            => $changeId,
                'sync_id'              => $this->syncId()
            ]
        );

        // Update or create coupon metadata
        if ( ! empty($coupon->metadata)) {
            foreach ($coupon->metadata as $key => $value) {
                MetadataModel::updateOrCreate(
                    ['stripe_id' => $coupon->id],
                    [
                        'key'       => $key,
                        'value'     => $value,
                        'type'      => 'coupon',
                        'change_id' => $changeId,
                        'sync_id'   => $this->syncId()
                    ]
                );
            }
        }

        // Delete coupon metadata that doesn't exist in Stripe
        MetadataModel::where('type', '=', 'coupon')
                     ->where('stripe_id', '=', $coupon->id)
                     ->where('change_id', '<>', $changeId)
                     ->delete();

        return $result->toArray();
    }

    /**
     * Delete coupon locally.
     *
     * @param $coupon
     * @return array
     */
    public function localDeleteCoupon($coupon)
    {
        $result = CouponModel::where('id', $coupon->id)->first();

        CouponModel::where('id', $coupon->id)->delete();

        MetadataModel::where('type', '=', 'coupon')
                     ->where('stripe_id', '=', $coupon->id)
                     ->delete();

        return $result;
    }
}
