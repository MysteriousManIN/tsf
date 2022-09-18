"use strict";

$(()=>{

	// const API_BASE = "https://fontfamilies.000webhostapp.com/tsf/bbs/api/";

	const _fetch = async (url, fd) => { return await (await fetch(url, { method: "post", body: fd })).json(); };

	const customer = async (id, options = {}) => {

		const fd = new FormData();
		fd.append("query", JSON.stringify({ id: id, options: options }));

		return await _fetch("../api/customer.php", fd);

	};

	const transaction = async (id) => {

		const fd = new FormData();
		fd.append("query", JSON.stringify({ id: id }));

		return await _fetch("../api/transaction.php", fd);

	};

	const transferMoney = async (sid, rid, amount) => {

		const fd = new FormData();
		fd.append("query", JSON.stringify({ sid: sid, rid: rid, amount: amount }));

		return await _fetch("../api/transfer-money.php", fd);

	};

	$.bbs = {
		customer: customer,
		transaction: transaction,
		transferMoney: transferMoney
	};

	Object.freeze($.bbs);

	$("#website-name").on("click", ()=>{
        window.location.assign("https://typefaces.com/bbs/");
    });

});