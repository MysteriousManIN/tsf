"use strict";

$(()=>{

	const custTableRow = (id, name, balance) => {

		let row = $("<div>").append(
			$("<span>").text(id),
			$("<div>").append(
				$("<span>").text(name),
				$("<span>").text(id)
			),
			$("<span>").html(`&#8377; ${balance}`)
		);

		row.on("click", ()=>{
			window.location.assign(`../cust/?c=${id}`);
		});

		return row;
	
	};

	const displayAllCustomers = async () => {

		let res = await $.bbs.customer("all", { balance: true }),
			data = res.result.data;

		$("#cust-table > div:nth-child(2)").html("");
		data.forEach((d)=>{
			let { id, name, balance } = d;
			$("#cust-table > div:nth-child(2)").append(custTableRow(id, name, balance));
		});
		
	};
	
	displayAllCustomers();

});