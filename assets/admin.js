import{r as h,j as e,a as j}from"./client.js";function k(){const[a,l]=h.useState([]),[u,n]=h.useState("");h.useEffect(()=>{p()},[]);async function m(t,r={}){const i=await fetch(t,{credentials:"include",...r}),d=await i.text();let c;try{c=JSON.parse(d)}catch{throw new Error("Invalid JSON: "+d)}return{res:i,data:c}}async function p(){try{const{res:t,data:r}=await m("/api/admin_list_tickets.php");if(!t.ok||r.success===!1)throw new Error(r.error||`HTTP ${t.status}`);l(r.tickets||[]),n("")}catch(t){n(`Could not load tickets: ${t.message}`)}}async function x(t,r){const d={1:"Open",2:"In Progress",3:"Closed"}[r],c=[...a];l(a.map(s=>s.id===t?{...s,status:d}:s));try{const{res:s,data:f}=await m("/api/update_ticket_status.php",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({id:Number(t),status_id:Number(r)})});if(!s.ok||f.success===!1)throw new Error(f.error);p()}catch(s){console.error("Update error:",s),n(`Failed to update: ${s.message}`),l(c)}}async function b(t){if(confirm("Delete this ticket permanently?"))try{const{res:r,data:i}=await m("/api/delete_ticket.php",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({id:t})});if(!r.ok||i.success===!1)throw new Error(i.error);p()}catch(r){n(`Failed to delete: ${r.message}`)}}const o={total:a.length,open:a.filter(t=>t.status==="Open").length,progress:a.filter(t=>t.status==="In Progress").length,closed:a.filter(t=>t.status==="Closed").length};return e.jsxs(e.Fragment,{children:[e.jsx("style",{children:`
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

        .header-flex {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: 3rem;
        }

        h1 {
          font-size: 2.5rem;
          background: linear-gradient(to top, #ff0844, #ffb199);
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
        }

        .logout-btn {
          background: #ff4d4d;
          color: white;
          padding: 10px 20px;
          text-decoration: none;
          border-radius: 4px;
          font-weight: bold;
        }

        .summary-grid {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
          gap: 1.5rem;
          margin-bottom: 3rem;
        }

        .stat-card {
          background: #1f1f1f;
          padding: 1.5rem;
          border-radius: 8px;
          text-align: center;
        }

        .stat-card h3 {
          font-size: 0.8rem;
          color: #aaa;
          margin-bottom: 0.5rem;
          text-transform: uppercase;
        }

        .stat-number {
          font-size: 2rem;
          font-weight: bold;
          color: #fe5196;
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
      `}),e.jsxs("div",{className:"page",children:[e.jsxs("div",{className:"header-flex",children:[e.jsx("h1",{children:"Admin Dashboard"}),e.jsx("a",{href:"/logout.php",className:"logout-btn",children:"Logout"})]}),e.jsxs("div",{className:"summary-grid",children:[e.jsxs("div",{className:"stat-card",children:[e.jsx("h3",{children:"Total"}),e.jsx("div",{className:"stat-number",children:o.total})]}),e.jsxs("div",{className:"stat-card",children:[e.jsx("h3",{children:"Open"}),e.jsx("div",{className:"stat-number",children:o.open})]}),e.jsxs("div",{className:"stat-card",children:[e.jsx("h3",{children:"In Progress"}),e.jsx("div",{className:"stat-number",children:o.progress})]}),e.jsxs("div",{className:"stat-card",children:[e.jsx("h3",{children:"Closed"}),e.jsx("div",{className:"stat-number",children:o.closed})]})]}),e.jsxs("div",{className:"card",children:[e.jsx("h2",{children:"All Tickets"}),u&&e.jsx("div",{className:"error",children:u}),a.map(t=>e.jsxs("div",{className:"ticket",children:[e.jsxs("div",{className:"ticket-header",children:[e.jsxs("div",{className:"ticket-title",children:["#",t.id," — ",t.title]}),e.jsxs("select",{value:t.status==="Open"?"1":t.status==="In Progress"?"2":"3",onChange:r=>x(t.id,r.target.value),children:[e.jsx("option",{value:"1",children:"Open"}),e.jsx("option",{value:"2",children:"In Progress"}),e.jsx("option",{value:"3",children:"Closed"})]})]}),e.jsx("div",{style:{color:"#aaa",fontSize:"0.9rem"},children:t.email}),e.jsx("div",{style:{margin:"10px 0"},children:t.message}),e.jsx("button",{className:"danger",onClick:()=>b(t.id),children:"Delete Ticket"})]},t.id))]})]})]})}const g=document.getElementById("root");g&&j(g).render(e.jsx(k,{}));
