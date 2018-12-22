<?php

namespace App\Http\Controllers;

use App\Models\Actor;
use App\Models\Address;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Film;
use App\Models\FilmActor;
use App\Models\FilmCategory;
use App\Models\Inventory;
use App\Models\Payment;
use App\Models\Rental;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Reliese\Database\Eloquent\Model;

class TestController extends Controller
{
    public function sample1()
    {
        $rRecently = Rental::select('customer_id')
            ->selectRaw("group_concat(f.title order by tmp.rental_date desc, ',') titles")
            ->selectRaw('max(tmp.rental_date) max_rental_date')
            ->fromSub(
                Rental::select('customer_id', 'inventory_id', 'rental_date')
                    ->selectRaw('row_number() over (partition by customer_id order by rental_date desc) num'),
                'tmp'
            )
            ->leftJoin(sprintf('%s as i', Inventory::make()->getTable()), 'tmp.inventory_id', 'i.inventory_id')
            ->leftJoin(sprintf('%s as f', Film::make()->getTable()), 'i.film_id', 'f.film_id')
            ->where('num', '<=', 3)
            ->groupBy('customer_id');

        $rAvg = Customer::select('customer_id')
            ->addSelect('cnt')
            ->addSelect('avg_all')
            ->addSelect('avg_country')
            ->addSelect('avg_city')
            ->selectRaw('if(cnt >= avg_all    , 1, 0) avg_flg')
            ->selectRaw('if(cnt >= avg_country, 1, 0) avg_country_flg')
            ->selectRaw('if(cnt >= avg_city   , 1, 0) avg_city_flg')
            ->fromSub(
                Customer::select('customer_total.customer_id')
                    ->addSelect('customer_total.cnt')
                    ->selectRaw('avg(cnt) over() avg_all')
                    ->selectRaw('avg(cnt) over(partition by co.country_id) avg_country')
                    ->selectRaw('avg(cnt) over(partition by ci.city_id) avg_city')
                    ->fromSub(
                        Rental::select('customer_id', DB::raw('count(*) cnt'))
                            ->groupBy('customer_id'),
                        'customer_total'
                    )
                    ->leftJoin(sprintf('%s as cu', Customer::make()->getTable()), 'customer_total.customer_id', 'cu.customer_id')
                    ->leftJoin(sprintf('%s as a', Address::make()->getTable()), 'cu.address_id', 'a.address_id')
                    ->leftJoin(sprintf('%s as ci', City::make()->getTable()), 'a.city_id', 'ci.city_id')
                    ->leftJoin(sprintf('%s as co', Country::make()->getTable()), 'ci.country_id', 'co.country_id'),
                'avg_base'
            );

        $customerRate = Customer::select('hoge.customer_id')
            ->selectRaw('sum(if(hoge.rate_partition = 1, hoge.rental_rate, 0)) g_rate_sum')
            ->selectRaw('sum(if(hoge.rate_partition = 0, hoge.rental_rate, 0)) l_rate_sum')
            ->selectRaw("max(case when hoge.rate_partition = 1 and hoge.num = 1 then g_c.name else '' end) g_rate_name")
            ->selectRaw("max(case when hoge.rate_partition = 0 and hoge.num = 1 then l_c.name else '' end) l_rate_name")
            ->fromSub(
                Rental::select('tmp.customer_id')
                    ->addSelect('tmp.rental_rate')
                    ->addSelect('tmp.rate_partition')
                    ->selectRaw('row_number() over(partition by tmp.customer_id, tmp.rate_partition order by rental_date desc) num')
                    ->selectRaw('fc_g_rate.category_id g_category_id')
                    ->selectRaw('fc_l_rate.category_id l_category_id')
                    ->fromSub(
                        Rental::select('customer_id', 'f.film_id', 'rental_date', 'rental_rate')
                            ->selectRaw('if(f.rental_rate >= 1, 1, 0) rate_partition')
                            ->selectRaw('count(*) over(partition by customer_id) cnt')
                            ->from(sprintf('%s as r', Rental::make()->getTable()))
                            ->leftJoin(sprintf('%s as i', Inventory::make()->getTable()), 'r.inventory_id', 'i.inventory_id')
                            ->leftJoin(sprintf('%s as f', Film::make()->getTable()), 'i.film_id', 'f.film_id'),
                        'tmp'
                    )
                    ->leftJoin(sprintf('%s as fc_g_rate', FilmCategory::make()->getTable()), function (JoinClause $join) {
                        $join->on('tmp.film_id', 'fc_g_rate.film_id');
                        $join->where('tmp.rate_partition', 1);
                        $join->where('tmp.cnt','>=', 20);
                    })
                    ->leftJoin(sprintf('%s as fc_l_rate', FilmCategory::make()->getTable()), function (JoinClause $join) {
                        $join->on('tmp.film_id', 'fc_l_rate.film_id');
                        $join->where('tmp.rate_partition', 0);
                        $join->where('tmp.cnt','>=', 25);
                    }),
                'hoge'
            )
            ->leftJoin(sprintf('%s as g_c', Category::make()->getTable()), 'hoge.g_category_id', 'g_c.category_id')
            ->leftJoin(sprintf('%s as l_c', Category::make()->getTable()), 'hoge.l_category_id', 'l_c.category_id')
            ->groupBy('hoge.customer_id');

        $data = Customer::select('cu.first_name')
            ->addSelect('cu.last_name')
            ->addSelect('co.country')
            ->addSelect('r_recently.titles')
            ->addSelect('r_recently.max_rental_date')
            ->addSelect('r_avg.cnt')
            ->addSelect('r_avg.avg_all')
            ->addSelect('r_avg.avg_country')
            ->addSelect('r_avg.avg_city')
            ->selectRaw("concat(r_avg.avg_flg,',',r_avg.avg_country_flg,',',r_avg.avg_city_flg)")
            ->selectRaw('greatest(r_avg.avg_flg,r_avg.avg_country_flg,r_avg.avg_city_flg)')
            ->addSelect('customer_rate.g_rate_sum')
            ->addSelect('customer_rate.l_rate_sum')
            ->addSelect('customer_rate.g_rate_name')
            ->addSelect('customer_rate.l_rate_name')
            ->from(sprintf('%s as cu', Customer::make()->getTable()))
            ->leftJoin(sprintf('%s as a', Address::make()->getTable()), 'cu.address_id', 'a.address_id')
            ->leftJoin(sprintf('%s as ci', City::make()->getTable()), 'a.city_id', 'ci.city_id')
            ->leftJoin(sprintf('%s as co', Country::make()->getTable()), 'ci.country_id', 'co.country_id')
            ->leftJoinSub($rRecently->toSql(), 'r_recently', 'cu.customer_id', 'r_recently.customer_id')
            ->leftJoinSub($rAvg->toSql(), 'r_avg', 'cu.customer_id', 'r_avg.customer_id')
            ->leftJoinSub($customerRate->toSql(), 'customer_rate', 'cu.customer_id', 'customer_rate.customer_id')
            ->addBinding($rRecently->getBindings())
            ->addBinding($customerRate->getBindings())
            ->get()
            ->toArray();

        $f = fopen('php://memory', 'r+');
        foreach ($data as $d) fputcsv($f, $d);
        rewind($f);
        return response(stream_get_contents($f), 200)
            ->header('Content-Type', 'text/plain');
    }

    public function sample2()
    {
        $months = Model::make()
            ->setTable(DB::raw('dual'))
            ->selectRaw("'2005-06-01'")
            ->union(
                Model::make()
                    ->setTable(DB::raw('months'))
                    ->selectRaw('date_add(mon, interval 1 month) added_month')
                    ->havingRaw("added_month <= '2005-08-01'")
            );
        $categoyMonths = Model::make()
            ->setTable(DB::raw('months'))
            ->crossJoin(Category::make()->getTable());
        $baseCustomerRental = Customer::select('cu.*')
            ->addSelect('ci.city_id')
            ->addSelect('ci.city')
            ->addSelect('co.country')
            ->addSelect('r.rental_id')
            ->addSelect('r.rental_date')
            ->addSelect('ca.name')
            ->from(sprintf('%s as cu', Customer::make()->getTable()))
            ->leftJoin(sprintf('%s as a', Address::make()->getTable()), 'cu.address_id', 'a.address_id')
            ->leftJoin(sprintf('%s as ci', City::make()->getTable()), 'a.city_id', 'ci.city_id')
            ->leftJoin(sprintf('%s as co', Country::make()->getTable()), 'ci.country_id', 'co.country_id')
            ->leftJoin(sprintf('%s as r', Rental::make()->getTable()), 'cu.customer_id', 'r.customer_id')
            ->leftJoin(sprintf('%s as i', Inventory::make()->getTable()), 'r.inventory_id', 'i.inventory_id')
            ->leftJoin(sprintf('%s as f', Film::make()->getTable()), 'i.film_id', 'f.film_id')
            ->leftJoin(sprintf('%s as fc', FilmCategory::make()->getTable()), 'f.film_id', 'fc.film_id')
            ->leftJoin(sprintf('%s as ca', Category::make()->getTable()), 'fc.category_id', 'ca.category_id');
        $zyogaiRental = Rental::select('rental_id')
            ->fromSub(
                Rental::select('rental_id')
                    ->selectRaw("count(*) over(partition by customer_id, date_format(rental_date,'%c')) cnt")
                    ->from('base_customer_rental')
                    ->whereIn('country', ['Brazil', 'China', 'Mexico'])
                    ->whereIn('name', ['Action', 'Drama']),
                'tmp'
            )
            ->where('cnt', '!=', 1)
            ->union(
                Rental::select('rental_id')
                    ->fromSub(
                        Rental::select('rental_id')
                            ->addSelect('cnt')
                            ->selectRaw('min(cnt) over() min')
                            ->fromSub(
                                Rental::select('rental_id')
                                    ->selectRaw("count(*) over(partition by city_id, date_format(rental_date,'%c')) cnt")
                                    ->from('base_customer_rental')
                                    ->whereIn('country', ['Brazil', 'China', 'Mexico', 'Russian Federation', 'South Africa'])
                                    ->whereIn('name', ['Action', 'Animation']),
                                'tmp'
                            )
                        ,'hoge'
                    )
                    ->where('cnt', DB::raw('min'))
            )
            ->union(
                Rental::select('rental_id')
                    ->fromSub(
                        Rental::select('rental_id')
                            ->selectRaw("if(name = 'Action' and date_format(rental_date,'%c') in (7,8), 1, 0) x")
                            ->selectRaw("if(name = 'Animation' and date_format(rental_date,'%c') in (6,7), 1, 0) y")
                            ->selectRaw("if(country = 'United States' and date_format(rental_date,'%c') between 6 and 8, 1, 0) z")
                            ->from('base_customer_rental')
                            ->whereIn('city', ['Vicente Lpez', 'Tandil', 'Stara Zagora', 'Vancouver', 'Richmond Hill', 'Oshawa', 'Warren', 'Tallahassee', 'Sunnyvale']),
                        'tmp'
                    )
                    ->where('x', 1)
                    ->orWhere(function (EloquentBuilder $query) {
                        $query->where('y', 1)
                            ->where('z', 0);
                    })
            );
        $rentalMonths = Category::selectRaw('sum(amount) sum_amount')
            ->addSelect('mon')
            ->addSelect('category_id')
            ->addSelect('store_partial')
            ->fromSub(
                Payment::select('p.amount')
                    ->selectRaw("date_format(p.payment_date ,'%Y-%m-01') mon")
                    ->addSelect('fc.category_id')
                    ->selectRaw('if(c.name is null, 0, s.store_id) store_partial')
                    ->from(sprintf('%s as p', Payment::make()->getTable()))
                    ->leftJoin(sprintf('%s as s', Staff::make()->getTable()), 'p.staff_id', 's.staff_id')
                    ->leftJoin(sprintf('%s as r', Rental::make()->getTable()), 'p.rental_id', 'r.rental_id')
                    ->leftJoin(sprintf('%s as i', Inventory::make()->getTable()), 'r.inventory_id', 'i.inventory_id')
                    ->leftJoin(sprintf('%s as f', Film::make()->getTable()), 'i.film_id', 'f.film_id')
                    ->leftJoin(sprintf('%s as fc', FilmCategory::make()->getTable()), 'f.film_id', 'fc.film_id')
                    ->leftJoinSub(Category::whereIn('name', ['Action', 'Animation', 'Children']), 'c', 'fc.category_id', 'c.category_id')
                    ->whereNotIn('r.rental_id', function (Builder $query) {
                        $query->from('zyogai_rental');
                    }),
                'tmp'
            )
            ->groupBy('mon', 'category_id', 'store_partial');
        $q = Model::make()->setTable('categoy_months as cm')
            ->select('cm.mon')
            ->addSelect('cm.name')
            ->selectRaw('if(rm.store_partial = 0, null, rm.store_partial)')
            ->addSelect('rm.sum_amount')
            ->addSelect('rm_zen.sum_amount as sum_amount_zen')
            ->selectRaw('rm.sum_amount - rm_zen.sum_amount')
            ->leftJoin('rental_months as rm', function (JoinClause $join) {
                $join->on('cm.mon', 'rm.mon');
                $join->on('cm.category_id', 'rm.category_id');
            })
            ->leftJoin('rental_months as rm_zen', function (JoinClause $join) {
                $join->on(DB::raw('date_add(cm.mon, interval -1 month)'), 'rm_zen.mon');
                $join->on('cm.category_id', 'rm_zen.category_id');
                $join->on('rm.store_partial', 'rm_zen.store_partial');
            })
            ->orderBy('cm.mon')
            ->orderBy('cm.category_id')
            ->orderBy('rm.store_partial');

        $data = DB::select(
            sprintf(<<<EOF
                with recursive months (mon) as (
                    %s
                )
                , categoy_months as (
                    %s
                )
                , base_customer_rental as ( 
                    %s
                )
                , zyogai_rental as (
                    %s
                )
                , rental_months as (
                    %s
                )
                %s
EOF
                ,$months->toSql()
                ,$categoyMonths->toSql()
                ,$baseCustomerRental->toSql()
                ,$zyogaiRental->toSql()
                ,$rentalMonths->toSql()
                ,$q->toSql()
            ),
            array_merge(
                $months->getBindings()
                ,$zyogaiRental->getBindings()
                ,$rentalMonths->getBindings()
            )
        );

        $f = fopen('php://memory', 'r+');
        foreach ($data as $d) fputcsv($f, (array) $d);
        rewind($f);
        return response(stream_get_contents($f), 200)
            ->header('Content-Type', 'text/plain');
    }

    public function sample3()
    {
        $hoge = Actor::select('actor_id')
            ->addSelect('cnt')
            ->addSelect('categories')
            ->selectRaw("if(categories like concat('%','Animation','%'), '○', '×') animation_flg")
            ->selectRaw("if(categories like concat('%','Children','%'), '○', '×') children_flg")
            ->fromSub(
                FilmActor::select('fa.actor_id')
                    ->selectRaw('count(*) cnt')
                    ->selectRaw(
                        sprintf(
                            'group_concat( distinct ( %s ) ) categories',
                            FilmCategory::selectSub(
                                $hogeCategory = Category::select('name')
                                    ->from(sprintf('%s as c', Category::make()->getTable()))
                                    ->whereRaw('c.category_id = fc.category_id')
                                    ->whereNotIn('c.name', ['Games','Horror','Music']),
                                'a'
                            )
                            ->from(sprintf('%s as fc', FilmCategory::make()->getTable()))
                            ->whereRaw('fc.film_id = fa.film_id')
                            ->toSql()
                        ),
                        $hogeCategory->getBindings()
                    )
                    ->from(sprintf('%s as fa', FilmActor::make()->getTable()))
                    ->groupBy('fa.actor_id'),
                'tmp'
            );

        $fugara = Actor::select('actor_id')
            ->selectRaw('max(if(num = 1, country_id, null)) first_country_id')
            ->selectRaw('max(if(num = 1, sum_amount, null)) first_sum_amount')
            ->selectRaw('max(if(num = 1, sum_country_amount, null)) first_sum_country_amount')
            ->selectRaw('max(if(num = 2, country_id, null)) second_country_id')
            ->selectRaw('max(if(num = 2, sum_amount, null)) second_sum_amount')
            ->selectRaw('max(if(num = 2, sum_country_amount, null)) second_sum_country_amount')
            ->selectRaw('max(if(num = 3, country_id, null)) third_country_id')
            ->selectRaw('max(if(num = 3, sum_amount, null)) third_sum_amount')
            ->selectRaw('max(if(num = 3, sum_country_amount, null)) third_sum_country_amount')
            ->fromSub(
                Country::select('piyo.num')
                    ->addSelect('piyo.country_id')
                    ->addSelect('piyo.sum_country_amount')
                    ->addSelect('fa.actor_id')
                    ->selectRaw('sum(p.amount) sum_amount')
                    ->fromSub(
                        Country::selectRaw('row_number() over() num')
                            ->addSelect('country_id')
                            ->addSelect('sum_country_amount')
                            ->fromSub(
                                Country::select('co.country_id')
                                    ->selectRaw('sum(amount) sum_country_amount')
                                    ->from(sprintf('%s as co', Country::make()->getTable()))
                                    ->leftJoin(sprintf('%s as ci', City::make()->getTable()), 'co.country_id', 'ci.country_id')
                                    ->leftJoin(sprintf('%s as a', Address::make()->getTable()), 'ci.city_id', 'a.city_id')
                                    ->leftJoin(sprintf('%s as cu', Customer::make()->getTable()), 'a.address_id', 'cu.address_id')
                                    ->leftJoin(sprintf('%s as r', Rental::make()->getTable()), 'cu.customer_id', 'r.customer_id')
                                    ->leftJoin(sprintf('%s as p', Payment::make()->getTable()), 'r.rental_id', 'p.rental_id')
                                    ->groupBy('co.country_id')
                                    ->havingRaw('count(*) <= ?', [1000])
                                    ->orderByRaw('count(*) desc')
                                    ->limit(3),
                                'fuga'
                            ),
                        'piyo'
                    )
                    ->leftJoin(sprintf('%s as ci', City::make()->getTable()), 'piyo.country_id', 'ci.country_id')
                    ->leftJoin(sprintf('%s as a', Address::make()->getTable()), 'ci.city_id', 'a.city_id')
                    ->leftJoin(sprintf('%s as cu', Customer::make()->getTable()), 'a.address_id', 'cu.address_id')
                    ->leftJoin(sprintf('%s as p', Payment::make()->getTable()), 'cu.customer_id', 'p.customer_id')
                    ->leftJoin(sprintf('%s as r', Rental::make()->getTable()), 'p.rental_id', 'r.rental_id')
                    ->leftJoin(sprintf('%s as i', Inventory::make()->getTable()), 'r.inventory_id', 'i.inventory_id')
                    ->leftJoin(sprintf('%s as f', Film::make()->getTable()), 'i.film_id', 'f.film_id')
                    ->leftJoin(sprintf('%s as fa', FilmActor::make()->getTable()), 'f.film_id', 'fa.film_id')
                    ->groupBy('piyo.num', 'piyo.country_id', 'fa.actor_id'),
                'hogera'
            )
            ->groupBy('actor_id');

        $data = Actor::selectRaw("concat(a.first_name,' ',a.last_name)")
            ->addSelect('hoge.cnt')
            ->addSelect('hoge.categories')
            ->addSelect('hoge.animation_flg')
            ->addSelect('hoge.children_flg')
            ->selectSub(Country::select('country')->where('country_id', DB::raw('fugara.first_country_id')), 'first_country')
            ->addSelect('fugara.first_sum_amount')
            ->selectRaw("concat(100 * (fugara.first_sum_amount / fugara.first_sum_country_amount), '%')")
            ->selectSub(Country::select('country')->where('country_id', DB::raw('fugara.second_country_id')), 'second_country')
            ->addSelect('fugara.second_sum_amount')
            ->selectRaw("concat(100 * (fugara.second_sum_amount / fugara.second_sum_country_amount), '%')")
            ->selectSub(Country::select('country')->where('country_id', DB::raw('fugara.third_country_id')), 'third_country')
            ->addSelect('fugara.third_sum_amount')
            ->selectRaw("concat(100 * (fugara.third_sum_amount / fugara.third_sum_country_amount), '%')")
            ->from(sprintf('%s as a', Actor::make()->getTable()))
            ->leftJoinSub($hoge->toSql(), 'hoge', 'a.actor_id', 'hoge.actor_id')
            ->leftJoinSub($fugara->toSql(), 'fugara', 'a.actor_id', 'fugara.actor_id')
            ->addBinding($hoge->getBindings())
            ->addBinding($fugara->getBindings())
            ->whereIn('a.actor_id', function (Builder $query) {
                $query->from(sprintf('%s as a', Actor::make()->getTable()))
                    ->select('a.actor_id')
                    ->leftJoin(sprintf('%s as fa', FilmActor::make()->getTable()), 'a.actor_id', 'fa.actor_id')
                    ->leftJoin(sprintf('%s as fc', FilmCategory::make()->getTable()), 'fa.film_id', 'fc.film_id')
                    ->joinSub(Category::where('name', 'Action'), 'c', 'fc.category_id', 'c.category_id');
            })
            ->whereRaw(
                sprintf(
                    'exists ( %s )',
                    FilmActor::selectRaw("'x'")
                        ->where('actor_id', DB::raw('a.actor_id'))
                        ->havingRaw('count(*) >= ?')
                        ->toSql()
                ),
                [20]
            )
            ->orderBy('a.actor_id')
            ->get()
            ->toArray();

        $f = fopen('php://memory', 'r+');
        foreach ($data as $d) fputcsv($f, $d);
        rewind($f);
        return response(stream_get_contents($f), 200)
            ->header('Content-Type', 'text/plain');
    }
}
