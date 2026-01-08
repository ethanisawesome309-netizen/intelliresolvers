import{r as s,j as t,a as g}from"./client.js";function h(){const[n,l]=s.useState([]),[c,a]=s.useState("");s.useEffect(()=>{o()},[]);async function o(){try{const e=await fetch("/api/admin_list_tickets.php",{credentials:"same-origin"});let r;try{r=await e.json()}catch{const i=await e.text();throw new Error("Invalid JSON response: "+i)}if(!e.ok||r.success===!1)throw new Error(r.error||`HTTP ${e.status}`);l(r.tickets||[]),a("")}catch(e){console.error("Fetch tickets error:",e),a(`Could not load tickets: ${e.message}`)}}async function m(e,r){try{const i=await fetch("/api/update_ticket_status.php",{method:"POST",headers:{"Content-Type":"application/json"},credentials:"same-origin",body:JSON.stringify({id:e,status:r})});if(!i.ok){const f=await i.text();throw new Error(`HTTP ${i.status}: ${f}`)}o()}catch(i){console.error("Update ticket error:",i),a(`Failed to update ticket: ${i.message}`)}}async function p(e){if(confirm("Delete this ticket permanently?"))try{const r=await fetch("/api/delete_ticket.php",{method:"POST",headers:{"Content-Type":"application/json"},credentials:"same-origin",body:JSON.stringify({id:e})});if(!r.ok){const i=await r.text();throw new Error(`HTTP ${r.status}: ${i}`)}o()}catch(r){console.error("Delete ticket error:",r),a(`Failed to delete ticket: ${r.message}`)}}return t.jsxs(t.Fragment,{children:[t.jsx("style",{children:`
        * {
          box-sizing: border-box;
          margin: 0;
          padding: 0;
          font-family: 'Kumbh Sans', sans-serif;
        }

        body {
          background: #141414;
          color: #fff;
        }

        .page {
          max-width: 1200px;
          margin: 0 auto;
          padding: 4rem 1rem;
        }

        h1 {
          text-align: center;
          font-size: 2.5rem;
          margin-bottom: 3rem;
          background: linear-gradient(to top, #ff0844, #ffb199);
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
        }

        h2 {
          margin-bottom: 1.2rem;
          font-size: 1.4rem;
        }

        .card {
          background: #1f1f1f;
          border-radius: 8px;
          padding: 2rem;
          margin-bottom: 2.5rem;
        }

        .ticket {
          background: #141414;
          border-radius: 6px;
          padding: 1.2rem;
          margin-bottom: 1rem;
        }

        .ticket-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: 0.4rem;
        }

        .ticket-title {
          font-weight: 600;
          background: linear-gradient(to top, #f77062, #fe5196);
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
        }

        .status {
          font-size: 0.75rem;
          padding: 4px 10px;
          border-radius: 999px;
          background: #1f1f1f;
          color: #f77062;
        }

        select, button {
          background: #141414;
          border: 1px solid #2a2a2a;
          color: #fff;
          padding: 6px 10px;
          border-radius: 6px;
        }

        .danger {
          color: #ff4d4d;
          cursor: pointer;
        }

        .error {
          color: #ff4d4d;
          margin-bottom: 1rem;
        }
      `}),t.jsxs("div",{className:"page",children:[t.jsx("h1",{children:"Admin Support Dashboard"}),t.jsxs("div",{className:"card",children:[t.jsx("h2",{children:"All Tickets"}),c&&t.jsx("div",{className:"error",children:c}),n.length===0?t.jsx("div",{className:"empty",children:"No tickets found."}):n.map(e=>t.jsxs("div",{className:"ticket",children:[t.jsxs("div",{className:"ticket-header",children:[t.jsxs("div",{className:"ticket-title",children:["#",e.id," — ",e.title]}),t.jsxs("select",{value:e.status,onChange:r=>m(e.id,r.target.value),children:[t.jsx("option",{children:"Open"}),t.jsx("option",{children:"In Progress"}),t.jsx("option",{children:"Closed"})]})]}),t.jsxs("div",{style:{marginBottom:"0.6rem",color:"#aaa"},children:["User: ",e.email]}),t.jsx("div",{children:e.message}),t.jsx("button",{className:"danger",style:{marginTop:"1rem"},onClick:()=>p(e.id),children:"Delete Ticket"})]},e.id))]})]})]})}const d=document.getElementById("root");d&&g(d).render(t.jsx(h,{}));
