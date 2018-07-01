<?php
namespace Home\Model;
use Think\Model;

class TicketModel extends Model 
{
	protected $insertFields = array('sp_name','sp_detail','shop_name','sp_price','sp_month_sales','common_income_ratio','common_income_money','is_promoting','event_income_ratio','event_income_money','event_begin','event_end','aliwangwang','tbk_short_link','tbk_link','tbk_tkl','yhq_total','yhq_surplus','yhq_value','yhq_begin','yhq_end','yhq_link','yhq_tkl','yhq_short_link','website');
	protected $updateFields = array('id','sp_name','sp_detail','shop_name','sp_price','sp_month_sales','common_income_ratio','common_income_money','is_promoting','event_income_ratio','event_income_money','event_begin','event_end','aliwangwang','tbk_short_link','tbk_link','tbk_tkl','yhq_total','yhq_surplus','yhq_value','yhq_begin','yhq_end','yhq_link','yhq_tkl','yhq_short_link','website');
	protected $_validate = array(
		array('sp_name', 'require', '商品名称不能为空！', 1, 'regex', 3),
		array('sp_name', '1,255', '商品名称的值最长不能超过 255 个字符！', 1, 'length', 3),
		array('sp_detail', 'require', '商品详情不能为空！', 1, 'regex', 3),
		array('sp_detail', '1,255', '商品详情的值最长不能超过 255 个字符！', 1, 'length', 3),
		array('shop_name', 'require', '店铺名称不能为空！', 1, 'regex', 3),
		array('shop_name', '1,255', '店铺名称的值最长不能超过 255 个字符！', 1, 'length', 3),
		array('sp_price', 'require', '商品价格，单位:元不能为空！', 1, 'regex', 3),
		array('sp_price', 'currency', '商品价格，单位:元必须是货币格式！', 1, 'regex', 3),
		array('sp_month_sales', 'number', '商品月销量必须是一个整数！', 2, 'regex', 3),
		array('common_income_ratio', 'require', '通用收入比率 %不能为空！', 1, 'regex', 3),
		array('common_income_ratio', 'currency', '通用收入比率 %必须是货币格式！', 1, 'regex', 3),
		array('common_income_money', 'require', '通用佣金不能为空！', 1, 'regex', 3),
		array('common_income_money', 'currency', '通用佣金必须是货币格式！', 1, 'regex', 3),
		array('is_promoting', '1,10', '活动状态的值最长不能超过 10 个字符！', 2, 'length', 3),
		array('event_income_ratio', 'require', '活动收入比率 %不能为空！', 1, 'regex', 3),
		array('event_income_ratio', 'currency', '活动收入比率 %必须是货币格式！', 1, 'regex', 3),
		array('event_income_money', 'require', '活动佣金不能为空！', 1, 'regex', 3),
		array('event_income_money', 'currency', '活动佣金必须是货币格式！', 1, 'regex', 3),
		array('aliwangwang', '1,255', '卖家旺旺的值最长不能超过 255 个字符！', 2, 'length', 3),
		array('tbk_short_link', '1,755', '淘宝客短链接,300天内有效的值最长不能超过 755 个字符！', 2, 'length', 3),
		array('tbk_link', '1,255', '淘宝客链接的值最长不能超过 255 个字符！', 2, 'length', 3),
		array('tbk_tkl', '1,255', '淘口令,30天内有效的值最长不能超过 255 个字符！', 2, 'length', 3),
		array('yhq_total', 'number', '优惠券总量必须是一个整数！', 2, 'regex', 3),
		array('yhq_surplus', 'number', '优惠券剩余量必须是一个整数！', 2, 'regex', 3),
		array('yhq_value', 'require', '优惠券面额不能为空！', 1, 'regex', 3),
		array('yhq_value', '1,50', '优惠券面额的值最长不能超过 50 个字符！', 1, 'length', 3),
		array('yhq_link', 'require', '优惠券链接不能为空！', 1, 'regex', 3),
		array('yhq_link', '1,255', '优惠券链接的值最长不能超过 255 个字符！', 1, 'length', 3),
		array('yhq_tkl', 'require', '优惠券淘口令不能为空！', 1, 'regex', 3),
		array('yhq_tkl', '1,30', '优惠券淘口令的值最长不能超过 30 个字符！', 1, 'length', 3),
		array('yhq_short_link', '1,75', '优惠券短链接(300天内有效)的值最长不能超过 75 个字符！', 2, 'length', 3),
		array('website', 'number', '来源网站,1tb,2pdd必须是一个整数！', 2, 'regex', 3),
	);

	/**
	 * 根据ID返回主图图片
	 * @param $ids
	 */
	public function getImgUrl($ids) {
		$imgs = $this->where(array(
			'id' => array('in', $ids),
		))->limit(8)
		->order('common_income_money DESC')
		->getField('id, sp_main_picture, sp_name');
		return $imgs;
	}
}