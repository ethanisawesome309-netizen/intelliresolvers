import{r as f,j as e,a as b}from"./client.js";function j(){const[a,l]=f.useState([]),[h,n]=f.useState("");f.useEffect(()=>{p()},[]);async function m(t,r={}){const s=await fetch(t,{credentials:"include",...r}),c=await s.text();try{return{res:s,data:JSON.parse(c)}}catch{throw new Error("Server returned an invalid response.")}}async function p(){try{const{res:t,data:r}=await m("/api/admin_list_tickets.php");if(!t.ok||r.success===!1)throw new Error(r.error||`HTTP ${t.status}`);l(r.tickets||[]),n("")}catch(t){n(`Load Error: ${t.message}`)}}async function g(t,r){const s=[...a],c=parseInt(r);l(o=>o.map(i=>i.id===t?{...i,status_id:c}:i));try{const{res:o,data:i}=await m("/api/update_ticket_status.php",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({id:t,status_id:c})});if(!o.ok||i.success===!1)throw new Error(i.error||"Unauthorized");p()}catch(o){l(s),n(`Update failed: ${o.message}`)}}async function x(t){if(confirm("Delete this ticket permanently?"))try{const{res:r,data:s}=await m("/api/delete_ticket.php",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({id:t})});s.success&&p()}catch(r){n(`Delete failed: ${r.message}`)}}const d={total:a.length,open:a.filter(t=>t.status_id===1).length,progress:a.filter(t=>t.status_id===2).length,closed:a.filter(t=>t.status_id===3).length};return e.jsxs(e.Fragment,{children:[e.jsx("style",{children:`
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
          border-left: 4px solid #333;
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
      `}),e.jsxs("div",{className:"page",children:[e.jsxs("div",{className:"header-flex",children:[e.jsx("h1",{children:"Admin Dashboard"}),e.jsx("a",{href:"/logout.php",className:"logout-btn",children:"Logout"})]}),e.jsxs("div",{className:"summary-grid",children:[e.jsxs("div",{className:"stat-card",children:[e.jsx("h3",{children:"Total"}),e.jsx("div",{className:"stat-number",children:d.total})]}),e.jsxs("div",{className:"stat-card",style:{borderColor:"#2ecc71",borderBottom:"3px solid #2ecc71"},children:[e.jsx("h3",{children:"Open"}),e.jsx("div",{className:"stat-number",children:d.open})]}),e.jsxs("div",{className:"stat-card",style:{borderColor:"#f1c40f",borderBottom:"3px solid #f1c40f"},children:[e.jsx("h3",{children:"In Progress"}),e.jsx("div",{className:"stat-number",children:d.progress})]}),e.jsxs("div",{className:"stat-card",style:{borderColor:"#e74c3c",borderBottom:"3px solid #e74c3c"},children:[e.jsx("h3",{children:"Closed"}),e.jsx("div",{className:"stat-number",children:d.closed})]})]}),h&&e.jsx("div",{style:{color:"#ff4d4d",marginBottom:"1rem"},children:h}),e.jsxs("div",{className:"card",children:[e.jsx("h2",{children:"All Tickets"}),a.map((t,r)=>e.jsxs("div",{className:"ticket",style:{borderColor:t.status_id===1?"#2ecc71":t.status_id===2?"#f1c40f":"#e74c3c"},children:[e.jsxs("div",{style:{display:"flex",justifyContent:"space-between",alignItems:"center"},children:[e.jsxs("div",{className:"ticket-title",children:["#",a.length-r," — ",t.title]}),e.jsxs("select",{value:t.status_id,onChange:s=>g(t.id,s.target.value),children:[e.jsx("option",{value:"1",children:"Open"}),e.jsx("option",{value:"2",children:"In Progress"}),e.jsx("option",{value:"3",children:"Closed"})]})]}),e.jsx("div",{style:{color:"#aaa",fontSize:"0.9rem",marginTop:"5px"},children:t.email}),e.jsx("div",{style:{margin:"15px 0",lineHeight:"1.6"},children:t.message}),e.jsx("button",{className:"danger",onClick:()=>x(t.id),children:"Delete Ticket"})]},t.id))]})]})]})}const u=document.getElementById("root");u&&b(u).render(e.jsx(j,{}));
