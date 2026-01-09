import{r as g,j as e,a as k}from"./client.js";function y(){const[n,p]=g.useState([]),[f,d]=g.useState("");g.useEffect(()=>{u()},[]);const r=t=>String(t||"").trim().toLowerCase();async function h(t,s={}){const a=await fetch(t,{credentials:"include",...s}),o=await a.text();let m;try{m=JSON.parse(o)}catch{throw new Error("Invalid JSON: "+o)}return{res:a,data:m}}async function u(){try{const{res:t,data:s}=await h("/api/admin_list_tickets.php");if(!t.ok||s.success===!1)throw new Error(s.error||`HTTP ${t.status}`);const a=(s.tickets||[]).map(o=>({...o,status:r(o.status)==="open"?"Open":r(o.status)==="in progress"?"In Progress":"Closed"}));p(a),d("")}catch(t){d(`Could not load tickets: ${t.message}`)}}async function b(t,s){const o={1:"Open",2:"In Progress",3:"Closed"}[s],m=[...n];p(i=>i.map(c=>c.id===t?{...c,status:o}:c));try{const{res:i,data:c}=await h("/api/update_ticket_status.php",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({id:Number(t),status_id:Number(s)})});if(!i.ok||c.success===!1)throw new Error(c.error||"Unauthorized");u()}catch(i){console.error("Update error:",i),d(`Update failed: ${i.message}`),p(m)}}async function j(t){if(confirm("Delete this ticket permanently?"))try{const{res:s,data:a}=await h("/api/delete_ticket.php",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({id:t})});if(!s.ok||a.success===!1)throw new Error(a.error);u()}catch(s){d(`Delete failed: ${s.message}`)}}const l={total:n.length,open:n.filter(t=>r(t.status)==="open").length,progress:n.filter(t=>r(t.status)==="in progress").length,closed:n.filter(t=>r(t.status)==="closed").length};return e.jsxs(e.Fragment,{children:[e.jsx("style",{children:`
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
      `}),e.jsxs("div",{className:"page",children:[e.jsxs("div",{className:"header-flex",children:[e.jsx("h1",{children:"Admin Dashboard"}),e.jsx("a",{href:"/logout.php",className:"logout-btn",children:"Logout"})]}),e.jsxs("div",{className:"summary-grid",children:[e.jsxs("div",{className:"stat-card",children:[e.jsx("h3",{children:"Total"}),e.jsx("div",{className:"stat-number",children:l.total})]}),e.jsxs("div",{className:"stat-card",children:[e.jsx("h3",{children:"Open"}),e.jsx("div",{className:"stat-number",children:l.open})]}),e.jsxs("div",{className:"stat-card",children:[e.jsx("h3",{children:"In Progress"}),e.jsx("div",{className:"stat-number",children:l.progress})]}),e.jsxs("div",{className:"stat-card",children:[e.jsx("h3",{children:"Closed"}),e.jsx("div",{className:"stat-number",children:l.closed})]})]}),e.jsxs("div",{className:"card",children:[e.jsx("h2",{children:"All Tickets"}),f&&e.jsx("div",{className:"error",children:f}),n.map(t=>e.jsxs("div",{className:"ticket",style:{borderColor:r(t.status)==="open"?"#2ecc71":r(t.status)==="in progress"?"#f1c40f":"#e74c3c"},children:[e.jsxs("div",{className:"ticket-header",children:[e.jsxs("div",{className:"ticket-title",children:["#",t.id," — ",t.title]}),e.jsxs("select",{value:r(t.status)==="open"?"1":r(t.status)==="in progress"?"2":"3",onChange:s=>b(t.id,s.target.value),children:[e.jsx("option",{value:"1",children:"Open"}),e.jsx("option",{value:"2",children:"In Progress"}),e.jsx("option",{value:"3",children:"Closed"})]})]}),e.jsx("div",{style:{color:"#aaa",fontSize:"0.9rem"},children:t.email}),e.jsx("div",{style:{margin:"10px 0",lineHeight:"1.4"},children:t.message}),e.jsx("button",{className:"danger",onClick:()=>j(t.id),children:"Delete Ticket"})]},t.id))]})]})]})}const x=document.getElementById("root");x&&k(x).render(e.jsx(y,{}));
