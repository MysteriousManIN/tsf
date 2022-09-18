"use strict";

$(()=>{

    const transactionTableRow = (id, name, amount, date_time, action) => {

		let d = new Date(date_time), a = d.toDateString(), b = d.toLocaleTimeString();

		let row = $("<div>", { taction: action }).append(
			$("<span>").text(id),
			$("<div>").append(
				$("<span>").text(name),
				$("<span>").text(`${a}, ${b}`)
			),
			$("<span>").html(`&#8377; ${amount}`)
		);

		return row;
	
	};

    const displayCustomerDetails = async (id) => {

		const displayBasic = async (id) => {

			$("#cust-basic-details > div").html("");

			let res = await $.bbs.customer(id, { balance: 1, email_id: 1 });

			if(res.result.code === 1){

				let { id:cid, name, email_id, balance } = res.result.data[0];

				$("#cust-basic-details > h2").text(name);

				Object.entries({ "ID": cid, "Email ID": email_id, "Balance": `&#8377; ${balance}` }).forEach(v=>{
					$("#cust-basic-details > div").append(
						$("<div>").append(
							$("<span>").html(v[0]),
							$("<span>").html(v[1])
						)
					);
				});

				let ct_btn = $("<button>", { class:"btn-p", value:"button" }).text("Create transaction");
				ct_btn.on("click", ()=>{
					$("#transaction-form > h3 + div[msg]").remove();
					$("#create-transaction-box").addClass("show");
					$("#transaction-form input[name='tid']").focus();
				});

				$("#cust-basic-details > div").append($("<div>").append(ct_btn));

				$("#cust-basic-details").attr("hidden", false);

				return true;

			}else{

				$("#cust-basic-details").after($("<div>", { class:"error-box" }).text(`Customer ID '${id}' does not exists.`));
				return false;

			}

		};

		const displayTransaction = async (id) => {

            $("#cust-transaction-details + .error-box").remove();
			$("#transaction-table > div:nth-child(2)").html("");
			
			let res = await $.bbs.transaction(id);

			if(res.result.code === 1){

				res.result.data.forEach(t=>{
				
					let { action, amount, customer, date_time } = t,
						{ id:tid, name } = customer;

					$("#transaction-table > div:nth-child(2)").append(transactionTableRow(tid, name, amount, date_time, action));
	
				});

				$("#cust-transaction-details").attr("hidden", false);

				return true;
				
			}else{

				$("#cust-transaction-details").after($("<div>", { class:"error-box" }).text(`Transaction records are empty.`));
				return false;

			}

		};

		const setupTransactionForm = async (id) => {

			$("#t-ids").html("");

			let res = await $.bbs.customer("all");

			res.result.data.forEach(v=>{
				let { id:tid, name } = v;
                if(tid != id) $("#t-ids").append($("<option>", { value:`${tid}, ${name}` }));
			});

			$("#transaction-form-close-btn").on("click", ()=>{
				$("#create-transaction-box").removeClass("show");
			});

			$("#transaction-form").on("submit", async (e)=>{

				e.preventDefault();

				let res, fd = {};

				const submitBtnDisabled = (t) => {

					$("#transaction-form button[type='submit']").attr("disabled", t);

					if(t){
						$("#transaction-form button[type='submit']").text("Please wait...");
					}else{
						$("#transaction-form button[type='submit']").text("Transfer money");
					}

				};

                const displayMsg = (msg, clas) => {

                    $("#transaction-form > h3 + div[msg]").remove();
                    $("#transaction-form > h3").after($("<div>", { class:clas, msg:true }).text(msg));
					submitBtnDisabled(false);
					
                };

				submitBtnDisabled(true);

				$("#transaction-form").serializeArray().forEach(v=>{ fd[v.name] = v.value; });

				if(fd["tid"] !== ""){
					fd["tid"] = fd["tid"].split(",")[0];
				}else{
					displayMsg("Please enter ID", "error-box");
					return;
				}

                if(fd["tamount"] !== ""){
                    fd["tamount"] = parseFloat(fd["tamount"]);
                    if(isNaN(fd["tamount"])){
                        displayMsg("Amount is invalid", "error-box");
                        return;
                    }
                }else{
                    displayMsg("Please enter amount", "error-box");
					return; 
                }

				res = await $.bbs.transferMoney(id, fd["tid"], fd["tamount"]);

				if(res.result.code === 1){

                    displayMsg("Transaction has been done", "success-box");
                    displayBasic(id);
					displayTransaction(id);

					$("#transaction-form")[0].reset();

				}else if(res.result.code === 2){

                    displayMsg("ID does not exists", "error-box");

                }else if(res.result.code === 3){

                    displayMsg("Balance is not sufficient", "error-box");

                }

			});

		};

		if(await displayBasic(id)){
			setupTransactionForm(id);
			displayTransaction(id);
		}
		
		document.title = `bbs. - ${id}`;

		$(".div-0").html(`<span class="a" >All customers</span><span>&#10093;</span><span>${id}</span>`);
		$(".div-0 > span:nth-child(1)").on("click", ()=>{  
            window.location.assign("../all-cust/");
        });

	};

    let cust_id = (new URLSearchParams(window.location.search)).get("c");

    displayCustomerDetails(cust_id);

});