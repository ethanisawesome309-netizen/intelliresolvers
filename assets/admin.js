import{r as f,j as e,a as j}from"./client.js";function k(){const[s,m]=f.useState([]),[u,n]=f.useState("");f.useEffect(()=>{h()},[]);async function p(t,r={}){const a=await fetch(t,{credentials:"include",...r}),d=await a.text();let l;try{l=JSON.parse(d)}catch{throw new Error("Invalid JSON: "+d)}return{res:a,data:l}}async function h(){try{const{res:t,data:r}=await p("/api/admin_list_tickets.php");if(!t.ok||r.success===!1)throw new Error(r.error||`HTTP ${t.status}`);m(r.tickets||[]),n("")}catch(t){n(`Could not load tickets: ${t.message}`)}}async function x(t,r){const d={1:"Open",2:"In Progress",3:"Closed"}[r],l=[...s];m(o=>o.map(i=>i.id===t?{...i,status:d}:i));try{const{res:o,data:i}=await p("/api/update_ticket_status.php",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({id:Number(t),status_id:Number(r)})});if(!o.ok||i.success===!1)throw new Error(i.error||"Unauthorized");h()}catch(o){console.error("Update error:",o),n(`Update failed: ${o.message}`),m(l)}}async function b(t){if(confirm("Delete this ticket permanently?"))try{const{res:r,data:a}=await p("/api/delete_ticket.php",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({id:t})});if(!r.ok||a.success===!1)throw new Error(a.error);h()}catch(r){n(`Delete failed: ${r.message}`)}}const c={total:s.length,open:s.filter(t=>t.status==="Open").length,progress:s.filter(t=>t.status==="In Progress").length,closed:s.filter(t=>t.status==="Closed").length};return e.jsxs(e.Fragment,{children:[e.jsx("style",{children:`
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
      `}),e.jsxs("div",{className:"page",children:[e.jsxs("div",{className:"header-flex",children:[e.jsx("h1",{children:"Admin Dashboard"}),e.jsx("a",{href:"/logout.php",className:"logout-btn",children:"Logout"})]}),e.jsxs("div",{className:"summary-grid",children:[e.jsxs("div",{className:"stat-card",children:[e.jsx("h3",{children:"Total"}),e.jsx("div",{className:"stat-number",children:c.total})]}),e.jsxs("div",{className:"stat-card",style:{borderColor:"#2ecc71"},children:[e.jsx("h3",{children:"Open"}),e.jsx("div",{className:"stat-number",children:c.open})]}),e.jsxs("div",{className:"stat-card",style:{borderColor:"#f1c40f"},children:[e.jsx("h3",{children:"In Progress"}),e.jsx("div",{className:"stat-number",children:c.progress})]}),e.jsxs("div",{className:"stat-card",style:{borderColor:"#e74c3c"},children:[e.jsx("h3",{children:"Closed"}),e.jsx("div",{className:"stat-number",children:c.closed})]})]}),e.jsxs("div",{className:"card",children:[e.jsx("h2",{children:"All Tickets"}),u&&e.jsx("div",{className:"error",children:u}),s.map(t=>e.jsxs("div",{className:"ticket",style:{borderColor:t.status==="Open"?"#2ecc71":t.status==="In Progress"?"#f1c40f":"#e74c3c"},children:[e.jsxs("div",{className:"ticket-header",children:[e.jsxs("div",{className:"ticket-title",children:["#",t.id," — ",t.title]}),e.jsxs("select",{value:t.status==="Open"?"1":t.status==="In Progress"?"2":"3",onChange:r=>x(t.id,r.target.value),children:[e.jsx("option",{value:"1",children:"Open"}),e.jsx("option",{value:"2",children:"In Progress"}),e.jsx("option",{value:"3",children:"Closed"})]})]}),e.jsx("div",{style:{color:"#aaa",fontSize:"0.9rem"},children:t.email}),e.jsx("div",{style:{margin:"10px 0",lineHeight:"1.4"},children:t.message}),e.jsx("button",{className:"danger",onClick:()=>b(t.id),children:"Delete Ticket"})]},t.id))]})]})]})}const g=document.getElementById("root");g&&j(g).render(e.jsx(k,{}));
