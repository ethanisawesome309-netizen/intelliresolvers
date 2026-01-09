import{r as l,j as e,a as x}from"./client.js";function b(){const[a,f]=l.useState([]),[m,i]=l.useState("");l.useEffect(()=>{c()},[]);async function d(t,r={}){const s=await fetch(t,{credentials:"include",...r}),o=await s.text();let p;try{p=JSON.parse(o)}catch{throw new Error("Invalid JSON: "+o)}return{res:s,data:p}}async function c(){try{const{res:t,data:r}=await d("/api/admin_list_tickets.php");if(!t.ok||r.success===!1)throw new Error(r.error||`HTTP ${t.status}`);f(r.tickets||[]),i("")}catch(t){console.error("Load tickets error:",t),i(`Could not load tickets: ${t.message}`)}}async function g(t,r){try{const{res:s,data:o}=await d("/api/update_ticket_status.php",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({id:t,status:r})});if(!s.ok||o.success===!1)throw new Error(o.error||`HTTP ${s.status}`);c()}catch(s){console.error("Update ticket error:",s),i(`Failed to update ticket: ${s.message}`)}}async function u(t){if(confirm("Delete this ticket permanently?"))try{const{res:r,data:s}=await d("/api/delete_ticket.php",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({id:t})});if(!r.ok||s.success===!1)throw new Error(s.error||`HTTP ${r.status}`);c()}catch(r){console.error("Delete ticket error:",r),i(`Failed to delete ticket: ${r.message}`)}}const n={total:a.length,open:a.filter(t=>{var r;return((r=t.status)==null?void 0:r.toLowerCase())==="open"}).length,progress:a.filter(t=>{var r;return((r=t.status)==null?void 0:r.toLowerCase())==="in progress"}).length,closed:a.filter(t=>{var r;return((r=t.status)==null?void 0:r.toLowerCase())==="closed"}).length};return e.jsxs(e.Fragment,{children:[e.jsx("style",{children:`
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
      `}),e.jsxs("div",{className:"page",children:[e.jsxs("div",{className:"header-flex",children:[e.jsx("h1",{children:"Admin Support Dashboard"}),e.jsx("a",{href:"/logout.php",className:"logout-btn",children:"Logout"})]}),e.jsxs("div",{className:"summary-grid",children:[e.jsxs("div",{className:"stat-card",children:[e.jsx("h3",{children:"Total Tickets"}),e.jsx("div",{className:"stat-number",children:n.total})]}),e.jsxs("div",{className:"stat-card",style:{borderBottom:"3px solid #2ecc71"},children:[e.jsx("h3",{children:"Open"}),e.jsx("div",{className:"stat-number",children:n.open})]}),e.jsxs("div",{className:"stat-card",style:{borderBottom:"3px solid #ffb199"},children:[e.jsx("h3",{children:"In Progress"}),e.jsx("div",{className:"stat-number",children:n.progress})]}),e.jsxs("div",{className:"stat-card",style:{borderBottom:"3px solid #ff0844"},children:[e.jsx("h3",{children:"Closed"}),e.jsx("div",{className:"stat-number",children:n.closed})]})]}),e.jsxs("div",{className:"card",children:[e.jsx("h2",{children:"All Tickets"}),m&&e.jsx("div",{className:"error",children:m}),a.length===0?e.jsx("div",{className:"empty",children:"No tickets found."}):a.map(t=>e.jsxs("div",{className:"ticket",children:[e.jsxs("div",{className:"ticket-header",children:[e.jsxs("div",{className:"ticket-title",children:["#",t.id," — ",t.title]}),e.jsxs("select",{value:t.status,onChange:r=>g(t.id,r.target.value),children:[e.jsx("option",{value:"Open",children:"Open"}),e.jsx("option",{value:"In Progress",children:"In Progress"}),e.jsx("option",{value:"Closed",children:"Closed"})]})]}),e.jsxs("div",{style:{marginBottom:"0.6rem",color:"#aaa"},children:["User: ",t.email]}),e.jsx("div",{style:{lineHeight:"1.5"},children:t.message}),e.jsx("button",{className:"danger",style:{marginTop:"1rem"},onClick:()=>u(t.id),children:"Delete Ticket"})]},t.id))]})]})]})}const h=document.getElementById("root");h&&x(h).render(e.jsx(b,{}));
