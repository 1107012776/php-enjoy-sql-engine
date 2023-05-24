#namespace("order")
#sql("insert")
#remark("注释：插入")
insert into `order`(`product_id`, `state`)
values (:product_id, :state);
#end
#sql("list")
#remark("注释：查询列表")
select *
from `order`
where product_id = :product_id;
#end
#sql("view")
#remark("注释：查询单个")
select *
from `order`
where id = :id;
#end
#sql("update")
#remark("注释：更新")
update `order`
set state = :update_state
where id = :id;
#end
#sql("delete")
#remark("注释：删除")
delete
from `order`
where product_id = :product_id;
#end
#end("order")