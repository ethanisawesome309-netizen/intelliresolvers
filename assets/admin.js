import{r as c,j as t,a as h}from"./client.js";function x(){const[d,f]=c.useState([]),[l,o]=c.useState("");c.useEffect(()=>{n()},[]);async function i(e,r={}){const a=await fetch(e,{credentials:"same-origin",...r}),s=await a.text();let m;try{m=JSON.parse(s)}catch{throw new Error("Invalid JSON: "+s)}return{res:a,data:m}}async function n(){try{const{res:e,data:r}=await i("/api/admin_list_tickets.php");if(!e.ok||r.success===!1)throw new Error(r.error||`HTTP ${e.status}`);f(r.tickets||[]),o("")}catch(e){console.error("Load tickets error:",e),o(`Could not load tickets: ${e.message}`)}}async function u(e,r){try{const{res:a,data:s}=await i("/api/update_ticket_status.php",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({id:e,status:r})});if(!a.ok||s.success===!1)throw new Error(s.error||`HTTP ${a.status}`);n()}catch(a){console.error("Update ticket error:",a),o(`Failed to update ticket: ${a.message}`)}}async function g(e){if(confirm("Delete this ticket permanently?"))try{const{res:r,data:a}=await i("/api/delete_ticket.php",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({id:e})});if(!r.ok||a.success===!1)throw new Error(a.error||`HTTP ${r.status}`);n()}catch(r){console.error("Delete ticket error:",r),o(`Failed to delete ticket: ${r.message}`)}}return t.jsxs(t.Fragment,{children:[t.jsx("style",{children:`
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
      `}),t.jsxs("div",{className:"page",children:[t.jsx("h1",{children:"Admin Support Dashboard"}),t.jsxs("div",{className:"card",children:[t.jsx("h2",{children:"All Tickets"}),l&&t.jsx("div",{className:"error",children:l}),d.length===0?t.jsx("div",{className:"empty",children:"No tickets found."}):d.map(e=>t.jsxs("div",{className:"ticket",children:[t.jsxs("div",{className:"ticket-header",children:[t.jsxs("div",{className:"ticket-title",children:["#",e.id," — ",e.title]}),t.jsxs("select",{value:e.status,onChange:r=>u(e.id,r.target.value),children:[t.jsx("option",{children:"Open"}),t.jsx("option",{children:"In Progress"}),t.jsx("option",{children:"Closed"})]})]}),t.jsxs("div",{style:{marginBottom:"0.6rem",color:"#aaa"},children:["User: ",e.email]}),t.jsx("div",{children:e.message}),t.jsx("button",{className:"danger",style:{marginTop:"1rem"},onClick:()=>g(e.id),children:"Delete Ticket"})]},e.id))]})]})]})}const p=document.getElementById("root");p&&h(p).render(t.jsx(x,{}));
